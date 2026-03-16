<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Leads;

class LeadUIController extends Controller
{
    /**
     * Display the Kanban board view.
     */
    public function kanbanView()
    {
        return view('leads_kanban');
    }

    /**
     * Fetch leads for the Kanban board — supports per-stage pagination.
     *
     * Query params:
     *   stage  (int)  - lead status integer (0,1,2,3,5,9). Omit for summary-only (counts).
     *   page   (int)  - page number (default 1)
     *   limit  (int)  - cards per page (default 15, max 50)
     */
    public function kanbanData(Request $request)
    {
        $stageMap = [
            'New'       => 0,
            'Contacted' => 1,
            'Qualified' => 2,
            'Proposal'  => 3,
            'Closed'    => 5,
            'Lost'      => 9,
        ];

        // Summary-only mode: return counts for all stages (no card data)
        if ($request->missing('stage')) {
            $counts = [];
            foreach ($stageMap as $label => $statusInt) {
                $counts[$label] = Leads::where('status', $statusInt)->count();
            }
            return response()->json(['counts' => $counts]);
        }

        // Per-stage paginated mode
        $stage  = (int) $request->get('stage', 0);
        $limit  = min((int) $request->get('limit', 15), 50);
        $page   = max((int) $request->get('page', 1), 1);
        $offset = ($page - 1) * $limit;

        $total = Leads::where('status', $stage)->count();

        $leads = Leads::where('status', $stage)
            ->orderBy('updated_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get(['id', 'name', 'company', 'mob', 'whatsapp', 'email', 'values', 'poc', 'source', 'purpose', 'score', 'status']);

        return response()->json([
            'data'      => $leads,
            'total'     => $total,
            'page'      => $page,
            'limit'     => $limit,
            'has_more'  => ($offset + $limit) < $total,
        ]);
    }

    /**
     * Update the status of a lead via Drag & Drop.
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'id'     => 'required|exists:leads,id',
            'status' => 'required|integer',
        ]);

        $lead = Leads::find($request->id);
        if ($lead) {
            $lead->status = $request->status;
            $lead->save();
            return response()->json(['success' => true, 'message' => 'Status updated']);
        }

        return response()->json(['success' => false, 'message' => 'Lead not found'], 404);
    }
}
