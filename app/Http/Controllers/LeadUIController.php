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
     * Fetch all leads structured for the Kanban board.
     */
    public function kanbanData(Request $request)
    {
        // Add auth or permissions check as necessary here
        $leads = Leads::orderBy('updated_at', 'desc')->get();
        return response()->json(['data' => $leads]);
    }

    /**
     * Update the status of a lead via Drag & Drop.
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:leads,id',
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
