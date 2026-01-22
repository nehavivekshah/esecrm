<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Leads;
use App\Models\Clients;
use App\Models\Proposals;
use Exception;

class NewLeadController extends Controller
{
    public function newleads(Request $request)
    {
        $today = Carbon::now()->format('Y-m-d H:i:s');
        $roles = session('roles');

        // Logic for AJAX Request (DataTable)
        if ($request->ajax()) {
            try {
                // 1. Base Query for Filtering & Counting
                $query = Leads::query();

                if (Auth::user()->role != 'master') {
                    $query->where('cid', '=', Auth::user()->cid);
                }

                // Filter by Status
                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }

                // Filter by Assigned User (Sales Rep)
                if ($request->filled('assign_user') || (Auth::user()->name && Auth::user()->role != 'master' && $roles->features != 'All')) {
                    $query->where('assigned', ($request->assign_user ?? Auth::user()->name));
                }

                // Global Search Logic
                $searchData = $request->input('search');
                $searchValue = is_array($searchData) ? ($searchData['value'] ?? '') : '';

                if (!empty($searchValue)) {
                    $query->where(function ($q) use ($searchValue) {
                        $q->where('name', 'like', "%{$searchValue}%")
                          ->orWhere('company', 'like', "%{$searchValue}%")
                          ->orWhere('mob', 'like', "%{$searchValue}%")
                          ->orWhere('email', 'like', "%{$searchValue}%")
                          ->orWhere('poc', 'like', "%{$searchValue}%");
                    });
                }

                $recordsTotal = $query->count();

                // 2. Fetch Data with Latest Comment Join
                // To avoid 500 errors in Strict Mode, we group by id and select specific aggregates
                $leads = $query->leftJoin('lead_comments', function($join) {
                        $join->on('leads.id', '=', 'lead_comments.lead_id')
                            ->whereIn('lead_comments.next_date', function ($sub) {
                                $sub->select(DB::raw('MAX(next_date)'))
                                    ->from('lead_comments')
                                    ->whereColumn('lead_comments.lead_id', 'leads.id');
                            });
                    })
                    ->select([
                        'leads.id', 'leads.name', 'leads.company', 'leads.mob', 'leads.email', 'leads.whatsapp', 
                        'leads.status', 'leads.created_at', 'leads.purpose', 'leads.values', 'leads.poc', 'leads.assigned',
                        DB::raw('MAX(lead_comments.next_date) as next_date'),
                        DB::raw('MAX(lead_comments.created_at) as last_talk'),
                        DB::raw("CASE 
                            WHEN leads.status = 1 AND MAX(lead_comments.next_date) <= '$today' THEN 1
                            WHEN leads.status = 0 THEN 2
                            WHEN leads.status = 1 THEN 3
                            WHEN leads.status = 9 THEN 4
                            ELSE 5 END as priority_order")
                    ])
                    ->groupBy('leads.id', 'leads.name', 'leads.company', 'leads.mob', 'leads.email', 'leads.whatsapp', 
                              'leads.status', 'leads.created_at', 'leads.purpose', 'leads.values', 'leads.poc', 'leads.assigned')
                    ->orderBy('priority_order', 'asc')
                    ->orderBy('next_date', 'asc')
                    ->offset($request->input('start', 0))
                    ->limit($request->input('length', 50))
                    ->get();

                $data = [];
                foreach ($leads as $lead) {
                    $statusMap = ['0' => 'Fresh', '1' => 'Follow Up', '5' => 'Converted', '9' => 'Loss'];
                    $statusClassMap = ['0' => 'bg-light border text-dark', '1' => 'bg-warning text-dark', '5' => 'bg-success', '9' => 'bg-danger'];
                    
                    $statusText = $statusMap[$lead->status] ?? 'Fresh';
                    $statusBadge = '<span class="badge '.($statusClassMap[$lead->status] ?? 'bg-secondary').'">'.$statusText.'</span>';
                    
                    // Row highlight for expired follow-ups
                    //$rowClass = ($lead->status == '1' && $lead->next_date && $lead->next_date < $today) ? 'table-alert bg-alert view selectrow' : '';
                    
                    $todayDate = date('Y-m-d');
                    $currentTime = date('His');
                
                    if ($lead->status == '5') {
                        $rowClass = 'table-success view selectrow';
                    } elseif ($lead->status == '9') {
                        $rowClass = 'table-danger view selectrow';
                    } elseif (
                        $lead->status == '1' &&
                        (
                            date('Y-m-d', strtotime($lead->next_date)) < $todayDate ||
                            (
                                date('Y-m-d', strtotime($lead->next_date)) == $todayDate &&
                                date('His', strtotime($lead->next_date)) < $currentTime
                            )
                        )
                    ) {
                        $rowClass = 'table-alert bg-alert view selectrow';
                    } elseif ($lead->status == '1') {
                        $rowClass = 'table-warning view selectrow';
                    } else {
                        $rowClass = 'table-white view selectrow';
                    }
                    
                    $leadEmail = (!empty($lead->email)) ? '<a href="mailto:'.$lead->email.'" class="btn btn-warning btn-sm" data-track-type="email" data-track-value="'.$lead->email.'"><i class="bx bx-envelope"></i></a>' : '';
                    $leadMob = (!empty($lead->mob)) ? '<a href="tel:+'.$lead->mob.'" class="btn btn-primary btn-sm" data-track-type="call" data-track-value="+'.$lead->mob.'"><i class="bx bx-phone"></i></a>' : '';
                    /*$leadWhatsapp = !empty($lead->whatsapp) ? '<a href="https://api.whatsapp.com/send/?phone=' . urlencode($lead->whatsapp) . '&text=' . urlencode( "🚀 *Grow Your Business with Our Digital Solutions!*\n\n" . "We specialize in *Website Design & Development, ERP, CRM, Mobile App Development, and SEO* Services.\n" . "Let us help you build, manage, and scale your business online.\n\n" . "📞 Call / WhatsApp: +91 95945 45556 / +91 96197 75533\n\nhttps://webbrella.com/website-design-and-development" ) . '&type=phone_number&app_absent=0" target="_blank" class="btn btn-success bg-success text-white btn-sm" title="WhatsApp" data-track-type="whatsapp" data-track-value="+' . htmlspecialchars($lead->whatsapp) . '"> <i class="bx bxl-whatsapp"></i> </a>' : '';*/
                    $leadWhatsapp = !empty($lead->whatsapp) 
                        ? '<a href="https://api.whatsapp.com/send/?phone=' . urlencode($lead->whatsapp) . 
                        '&text=' . urlencode(
                        "🚀 *Grow Your Business with Our Digital Solutions*\n\n" .
                        "✅ Website Design & Development\n" .
                        "✅ ERP & CRM Solutions\n" .
                        "✅ Mobile App Development\n" .
                        "✅ SEO & Digital Growth Services\n\n" .
                        "🎁 *FREE with Our Services (Limited-Time Value Add):*\n" .
                        "🔹 SMS Pilot – Reach your customers instantly with promotional & transactional SMS\n" .
                        "🔹 Digital Visiting Card – Share your professional profile anytime, anywhere with one click\n" .
                        "🔹 Sales Lead Management – Track, manage, and convert leads more efficiently\n\n" .
                        "📞 *Call / WhatsApp:*\n" .
                        "+91 95945 45556 | +91 96197 75533\n\n" .
                        "🌐 *Learn more:*\n" .
                        "https://webbrella.com/website-design-and-development"
                        ) . 
                        '&type=phone_number&app_absent=0" 
                        target="_blank" 
                        class="btn btn-success bg-success text-white btn-sm" 
                        title="WhatsApp" 
                        data-track-type="whatsapp" 
                        data-track-value="+' . htmlspecialchars($lead->whatsapp) . '">
                        <i class="bx bxl-whatsapp"></i>
                        </a>' 
                        : '';

                    
                    $company = (!empty($lead->company)) ? '<br><small class="text-muted d-none">'.e($lead->company).'</small>' : '';
                    $poc = ($roles->features != 'All') ? e($lead->poc) : e($lead->assigned);
                    
                    $data[] = [
                        'checkbox'  => '<input type="checkbox" class="checklead" value="'.$lead->id.'">',
                        'name'      => e($lead->name).''.$company,
                        'company'   => e(substr($lead->company, 0, 20)),
                        'mobile'    => e("+".$lead->mob),
                        'status'    => $statusBadge,
                        'since'     => date('d M, Y', strtotime($lead->created_at)),
                        'purpose'   => e($lead->purpose),
                        'value'     => '₹' . number_format((float)$lead->values, 0),
                        'last_talk' => $lead->last_talk ? date('d M, Y', strtotime($lead->last_talk)) : '--',
                        'next_move' => $lead->next_date ? date('d M, h:i A', strtotime($lead->next_date)) : '--',
                        'assigned'       => $poc,
                        'action'    => '<div class="table-btn">
                                            '.$leadWhatsapp.'
                                            '.$leadMob.'
                                            '.$leadEmail.'
                                        </div>',
                        'row_class' => $rowClass,
                        'id'        => $lead->id
                    ];
                }

                return response()->json([
                    "draw"            => intval($request->draw),
                    "recordsTotal"    => intval($recordsTotal),
                    "recordsFiltered" => intval($recordsTotal),
                    "data"            => $data
                ]);

            } catch (Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        // Logic for Initial Page Load
        $getUsers = User::where('cid', Auth::user()->cid)->where('status', '1')->get();
        return view('newleads', compact('getUsers'));
    }

    public function bulkAssign(Request $request)
    {
        $request->validate(['leads' => 'required|array', 'user_id' => 'required']);
        $user = User::findOrFail($request->user_id);
        Leads::whereIn('id', $request->leads)->update(['assigned' => $user->name]);
        return response()->json(['status' => 'success']);
    }
    
    // 1. Fetch Lead Details & Comments for the Modal
    public function getLeadDetails($id)
    {
        $lead = Leads::findOrFail($id);
        $comments = DB::table('lead_comments')
                    ->where('lead_id', $id)
                    ->orderBy('created_at', 'desc')
                    ->get();
    
        return response()->json([
            'lead' => $lead,
            'comments' => $comments
        ]);
    }
    
    // 2. Update Lead Profile from Modal
    public function updateLead(Request $request)
    {
        $request->validate([
            'id'   => 'required|exists:leads,id',
            'name' => 'required',
            'mob'  => 'required',
        ]);
    
        DB::transaction(function () use ($request) {
    
            $lead = Leads::findOrFail($request->id);
    
            $data = $request->only([
                'name', 'company', 'email', 'mob', 'gstno', 'purpose',
                'assigned', 'poc', 'status', 'whatsapp', 'position',
                'industry', 'website', 'language', 'values', 'tags'
            ]);
    
            if ($request->filled('address')) {
                $data['location'] = json_encode($request->address);
            }
    
            $lead->update($data);
    
            // Convert Lead → Client
            if ((int)$request->status === 5) {
    
                // Prevent duplicate clients
                if (!Clients::where('commentLeadID', $lead->id)->exists()) {
    
                    $client = new Clients();
                    $client->cid           = Auth::user()->cid ?? '';
                    $client->commentLeadID = $lead->id;
                    $client->name          = $request->name;
                    $client->email         = $request->email;
                    $client->mob           = $request->mob;
                    $client->gstno         = $request->gstno;
                    $client->whatsapp      = $request->whatsapp;
                    $client->company       = $request->company;
                    $client->position      = $request->position;
                    $client->industry      = $request->industry;
                    $client->location      = json_encode($request->address) ?? null;
                    $client->website       = $request->website;
                    $client->purpose       = $request->purpose;
                    $client->values        = $request->values;
                    $client->language      = $request->language;
                    $client->poc           = $request->poc;
                    $client->tags          = $request->tags;
                    $client->status        = 0;
                    $client->save();
    
                    // Update proposal
                    $proposal = Proposals::where('lead_id', $lead->id)->first();
                    if ($proposal) {
                        $proposal->lead_id = $client->id;
                        $proposal->related = 2;
                        $proposal->save();
                    }
    
                    // Delete lead
                    $lead->delete();
                }
            }
        });
    
        return response()->json([
            'status'  => 'success',
            'message' => 'Lead updated successfully'
        ]);
    }
    
    public function deleteLead(Request $request)
    {
        // Validate that the ID exists
        $request->validate([
            'id' => 'required|exists:leads,id'
        ]);
    
        try {
            $lead = Leads::findOrFail($request->id);
            $lead->delete();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Lead deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete lead'
            ], 500);
        }
    }
    
    // 3. Save New Comment/Reminder
    public function storeComment(Request $request)
    {
        $request->validate([
            'lead_id' => 'required',
            'msg' => 'required',
            'next_date' => 'required'
        ]);
    
        DB::table('lead_comments')->insert([
            'lead_id' => $request->lead_id,
            'msg' => $request->msg,
            'next_date' => $request->next_date,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        // Update lead status to "Follow Up" (1) if it was Fresh (0)
        $lead = Leads::find($request->lead_id);
        if($lead->status == 0) {
            $lead->status = 1;
            $lead->save();
        }
    
        return response()->json(['status' => 'success']);
    }
}