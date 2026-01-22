<?php

namespace App\Http\Controllers;

use App\Models\Invoices;
use App\Models\Invoice_items;
use App\Models\Activity;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceApiController extends Controller
{
    /**
     * Get all invoices with filters
     */
    public function index(Request $request)
    {
        $query = Invoices::query();

        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('invoice_number', 'like', "%{$search}%");
        }

        $invoices = $query->with(['client', 'company', 'items'])
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'message' => 'Invoices retrieved successfully',
            'data' => $invoices,
        ]);
    }

    /**
     * Create a new invoice
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'company_id' => 'required|exists:companies,id',
            'invoice_number' => 'required|unique:invoices',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after:invoice_date',
            'status' => 'nullable|in:draft,sent,paid,overdue',
            'currency' => 'nullable|string|max:3',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
        ]);

        $items = $validated['items'];
        unset($validated['items']);

        $invoice = Invoices::create($validated);

        // Create line items
        $subtotal = 0;
        foreach ($items as $item) {
            $item['total'] = $item['quantity'] * $item['unit_price'];
            $subtotal += $item['total'];
            Invoice_items::create(array_merge($item, ['invoice_id' => $invoice->id]));
        }

        $invoice->update([
            'subtotal' => $subtotal,
            'total' => $subtotal + ($validated['tax'] ?? 0),
        ]);

        Activity::create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'entity_type' => 'Invoice',
            'entity_id' => $invoice->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'message' => 'Invoice created successfully',
            'data' => $invoice->load('client', 'company', 'items'),
        ], 201);
    }

    /**
     * Get a single invoice
     */
    public function show($id)
    {
        $invoice = Invoices::with(['client', 'company', 'items'])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Invoice retrieved successfully',
            'data' => $invoice,
        ]);
    }

    /**
     * Update an invoice
     */
    public function update(Request $request, $id)
    {
        $invoice = Invoices::findOrFail($id);

        $validated = $request->validate([
            'due_date' => 'sometimes|date|after:' . $invoice->invoice_date,
            'status' => 'sometimes|in:draft,sent,paid,overdue',
            'notes' => 'sometimes|string',
            'tax' => 'sometimes|numeric|min:0',
        ]);

        $invoice->update($validated);
        $invoice->calculateTotal();

        Activity::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'entity_type' => 'Invoice',
            'entity_id' => $id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'message' => 'Invoice updated successfully',
            'data' => $invoice->load('client', 'company', 'items'),
        ]);
    }

    /**
     * Delete an invoice
     */
    public function destroy($id)
    {
        $invoice = Invoices::findOrFail($id);

        Activity::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'entity_type' => 'Invoice',
            'entity_id' => $id,
            'ip_address' => request()->ip(),
        ]);

        $invoice->delete();

        return response()->json([
            'message' => 'Invoice deleted successfully',
        ]);
    }

    /**
     * Generate PDF for invoice
     */
    public function generatePdf($id)
    {
        $invoice = Invoices::with(['client', 'company', 'items'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $invoice]);

        return $pdf->download('invoice_' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Mark invoice as sent
     */
    public function markSent($id)
    {
        $invoice = Invoices::findOrFail($id);
        $invoice->update(['status' => 'sent']);

        return response()->json([
            'message' => 'Invoice marked as sent',
            'data' => $invoice,
        ]);
    }

    /**
     * Mark invoice as paid
     */
    public function markPaid($id)
    {
        $invoice = Invoices::findOrFail($id);
        $invoice->update(['status' => 'paid']);

        return response()->json([
            'message' => 'Invoice marked as paid',
            'data' => $invoice,
        ]);
    }

    /**
     * Get invoice statistics
     */
    public function statistics(Request $request)
    {
        $query = Invoices::query();

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $stats = [
            'total_invoices' => $query->count(),
            'draft_invoices' => (clone $query)->where('status', 'draft')->count(),
            'sent_invoices' => (clone $query)->where('status', 'sent')->count(),
            'paid_invoices' => (clone $query)->where('status', 'paid')->count(),
            'overdue_invoices' => (clone $query)->where('status', 'overdue')->count(),
            'total_value' => (clone $query)->sum('total'),
            'paid_value' => (clone $query)->where('status', 'paid')->sum('total'),
            'pending_value' => (clone $query)->whereIn('status', ['draft', 'sent', 'overdue'])->sum('total'),
            'avg_invoice_value' => $query->count() > 0 ? (clone $query)->avg('total') : 0,
        ];

        return response()->json([
            'message' => 'Invoice statistics retrieved',
            'data' => $stats,
        ]);
    }
}
