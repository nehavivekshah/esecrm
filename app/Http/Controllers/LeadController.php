<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\CustomMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\AuthController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

use Carbon\Carbon;
use App\Models\SmtpSettings;
use App\Models\Companies;
use App\Models\User;
use App\Models\Leads;
use App\Models\Clients;
use App\Models\Lead_comments;
use App\Models\Proposals;
use App\Models\Proposal_items;
use App\Models\Proposal_signatures;
use Exception;
use DateTime; 

class LeadController extends Controller
{
    /*public function leads(Request $request)
    {
        // Build the base query
        $query = Leads::leftJoin('lead_comments', function($join) {
                $join->on('leads.id', '=', 'lead_comments.lead_id')
                    ->whereIn('lead_comments.next_date', function ($query) {
                        $query->select(DB::raw('MAX(next_date)'))
                            ->from('lead_comments')
                            ->whereColumn('lead_comments.lead_id', 'leads.id');
                    });
            })
            ->select(
                'leads.id', 
                'leads.cid', 
                'leads.name', 
                'leads.company', 
                'leads.email', 
                'leads.mob', 
                'leads.gstno', 
                'leads.whatsapp', 
                'leads.location', 
                'leads.purpose', 
                'leads.assigned',   
                'leads.values',
                'leads.poc', 
                'leads.status', 
                'leads.created_at', 
                'leads.updated_at',
                'lead_comments.msg',
                DB::raw('MAX(lead_comments.next_date) as next_date'),
                DB::raw('MAX(lead_comments.created_at) as last_talk')
            )
            ->groupBy(
                'leads.id', 
                'leads.cid', 
                'leads.name', 
                'leads.company', 
                'leads.email', 
                'leads.mob', 
                'leads.gstno', 
                'leads.whatsapp', 
                'leads.location', 
                'leads.purpose', 
                'leads.assigned',   
                'leads.values',
                'leads.poc', 
                'leads.status', 
                'leads.created_at', 
                'leads.updated_at',
                'lead_comments.msg'
            );
    
        // Filter by CID if not master
        if (Auth::user()->role != 'master') {
            $query->where('leads.cid', '=', Auth::user()->cid);
        }
    
        // Apply search
        $search = $request->input('search');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('leads.name', 'like', "%{$search}%")
                    ->orWhere('leads.company', 'like', "%{$search}%")
                    ->orWhere('leads.email', 'like', "%{$search}%")
                    ->orWhere('leads.mob', 'like', "%{$search}%")
                    ->orWhere('leads.whatsapp', 'like', "%{$search}%")
                    ->orWhere('leads.location', 'like', "%{$search}%")
                    ->orWhere('leads.purpose', 'like', "%{$search}%")
                    ->orWhere('leads.assigned', 'like', "%{$search}%")
                    ->orWhere('leads.values', 'like', "%{$search}%")
                    ->orWhere('leads.poc', 'like', "%{$search}%")
                    ->orWhere('lead_comments.msg', 'like', "%{$search}%");
            });
        }
        
        // Apply status filter
        $status = $request->input('status');
        if ($status !== null) {
            $query->where(function ($q) use ($status) {
                $q->where('leads.status', '=', $status);
            });
        }
    
        // Get all leads
        $allLeads = $query->get();
    
        // Today's date
        $today = Carbon::today();
    
        // Sort the leads by your custom logic
        $allLeads = $allLeads->sortBy(function ($lead) use ($today) {
            $nextDate = $lead->next_date ? Carbon::parse($lead->next_date) : null;
    
            // Priority logic
            if ($lead->status == 1 && $nextDate && $nextDate->lte($today)) {
                $priority = 0;
            } elseif ($lead->status == 0) {
                $priority = 1;
            } elseif ($lead->status == 1 && $nextDate && $nextDate->gt($today)) {
                $priority = 2;
            } else {
                $priority = 3;
            }
    
            // Secondary sort: by next_date (closest first)
            $timestamp = $nextDate ? $nextDate->timestamp : PHP_INT_MAX;
    
            return [$priority, $timestamp];
        })->values();
    
        // Prepare reminder timestamps
        $reminderTimes = $allLeads->map(function ($lead) {
            return $lead->next_date ? Carbon::parse($lead->next_date)->timestamp * 1000 : null;
        });
    
        // Pagination manually
        $perPage = 50;
        $currentPage = $request->input('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $totalLeads = $allLeads->count();
        $leads = $allLeads->slice($offset, $perPage);
        $totalPages = (int) ceil($totalLeads / $perPage);
    
        // Active users of the same company
        $getUsers = User::where('cid', '=', Auth::user()->cid)
            ->where('status', '=', '1')
            ->get();
    
        return view('leads', [
            'leads'         => $leads,
            'reminderTimes' => $reminderTimes,
            'currentPage'   => $currentPage,
            'totalPages'    => $totalPages,
            'totalLeads'    => $totalLeads,
            'perPage'       => $perPage,
            'search'        => $search,
            'getUsers'      => $getUsers
        ]);
    }*/
    
    public function leads(Request $request)
    {
        $today = Carbon::now()->format('Y-m-d H:i:s'); // include date & time
    
        // Build the base query
        $query = Leads::leftJoin('lead_comments', function($join) {
                $join->on('leads.id', '=', 'lead_comments.lead_id')
                    ->whereIn('lead_comments.next_date', function ($query) {
                        $query->select(DB::raw('MAX(next_date)'))
                            ->from('lead_comments')
                            ->whereColumn('lead_comments.lead_id', 'leads.id');
                    });
            })
            ->select(
                'leads.id', 
                'leads.cid', 
                'leads.name', 
                'leads.company', 
                'leads.email', 
                'leads.mob', 
                'leads.gstno', 
                'leads.whatsapp', 
                'leads.location', 
                'leads.purpose', 
                'leads.assigned',   
                'leads.values',
                'leads.poc', 
                'leads.status', 
                'leads.created_at', 
                'leads.updated_at',
                'lead_comments.msg',
                DB::raw('MAX(lead_comments.next_date) as next_date'),
                DB::raw('MAX(lead_comments.created_at) as last_talk'),
    
                // Priority column
                DB::raw("
                    CASE
                        WHEN leads.status = 1 AND MAX(lead_comments.next_date) <= '$today' THEN 1  -- Urgent Followup
                        WHEN leads.status = 0 THEN 2                                             -- Fresh
                        WHEN leads.status = 1 THEN 3                                             -- Normal Followup
                        WHEN leads.status = 9 THEN 4                                             -- Lost
                        ELSE 5                                                                   -- Others
                    END as priority_order
                ")
            )
            ->groupBy(
                'leads.id', 
                'leads.cid', 
                'leads.name', 
                'leads.company', 
                'leads.email', 
                'leads.mob', 
                'leads.gstno', 
                'leads.whatsapp', 
                'leads.location', 
                'leads.purpose', 
                'leads.assigned',   
                'leads.values',
                'leads.poc', 
                'leads.status', 
                'leads.created_at', 
                'leads.updated_at',
                'lead_comments.msg'
            );
    
        // Filter by CID if not master
        if (Auth::user()->role != 'master') {
            $query->where('leads.cid', '=', Auth::user()->cid);
        }
    
        // Apply search
        $search = $request->input('search');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('leads.name', 'like', "%{$search}%")
                    ->orWhere('leads.company', 'like', "%{$search}%")
                    ->orWhere('leads.email', 'like', "%{$search}%")
                    ->orWhere('leads.mob', 'like', "%{$search}%")
                    ->orWhere('leads.whatsapp', 'like', "%{$search}%")
                    ->orWhere('leads.location', 'like', "%{$search}%")
                    ->orWhere('leads.purpose', 'like', "%{$search}%")
                    ->orWhere('leads.assigned', 'like', "%{$search}%")
                    ->orWhere('leads.values', 'like', "%{$search}%")
                    ->orWhere('leads.poc', 'like', "%{$search}%")
                    ->orWhere('leads.tags', 'like', "%{$search}%")
                    ->orWhere('lead_comments.msg', 'like', "%{$search}%");
            });
        }
    
        // Apply status filter
        $status = $request->input('status');
        if ($status !== null) {
            $query->where('leads.status', '=', $status);
        }
    
        // Order: urgent followup first (priority_order ASC), then by next_date ASC
        $query->orderBy('priority_order', 'asc')
              ->orderBy(DB::raw('MAX(lead_comments.next_date)'), 'asc');
    
        // Pagination
        $perPage = $request->rowcount ?? 50;
        $leads = $query->paginate($perPage);
    
        // Active users of the same company
        $getUsers = User::where('cid', '=', Auth::user()->cid)
            ->where('status', '=', '1')
            ->get();
    
        return view('leads', [
            'leads'         => $leads,
            'reminderTimes' => $leads->map(fn($lead) => $lead->next_date ? Carbon::parse($lead->next_date)->timestamp * 1000 : null),
            'currentPage'   => $leads->currentPage(),
            'totalPages'    => $leads->lastPage(),
            'totalLeads'    => $leads->total(),
            'perPage'       => $perPage,
            'search'        => $search,
            'getUsers'      => $getUsers
        ]);
    }

    public function leadList(Request $request)
    {
        $leads = Leads::select('id','name','company','email','mob','location')->where('cid', '=', Auth::user()->cid)->where('name', '!=', '')->orderBy('name','ASC')->get();
		    
	    return json_encode(['leads'=>$leads]);
    }
    
    public function singleLeadsGet(Request $request)
    {
        $id = ($request->id ?? '');
        $page = ($request->pagename ?? '');
        if($page=='leads'){
            
            $leads = Leads::where('id', '=', $request->id)->first();

		    $leadComments = Lead_comments::where('lead_id', '=', ($leads->id ?? ''))->get();
		    
		    $proposals = Proposals::leftJoin('leads','proposals.lead_id','=','leads.id')
                ->select('leads.name as lead_name','proposals.*')
                ->where('leads.id', '=', $id)
                ->orderBy('id','DESC')->get();
		    
		    return json_encode(['leads'=>$leads,'leadComments'=>$leadComments,'proposals'=>$proposals]);
        }
    }
    
    public function manageLead(Request $request)
    {

		$leads = Leads::where('id', '=', $request->id)->first();

		$leadComments = Lead_comments::where('lead_id', '=', ($leads->id ?? ''))->get();
        
        return view('manageLead',['leads'=>$leads,'leadComments'=>$leadComments]);
        
    }
    
    public function manageLeadPost(Request $request)
    {
        
        $location = json_encode($request->address ?? '');
            
        $currentPage = $request->page ?? 1;
        
        if(empty($request->id)){
            
            $leadSingle = new Leads();
            
            // Check if the mobile number already exists
            $existingLead = Leads::where('mob', $request->mob)->first();
            if ($existingLead) {
                return back()->with('error', 'Mobile number already exists in the leads table.');
            }
    
            $leadSingle->cid = (Auth::user()->cid ?? '');
            $leadSingle->name = ($request->name ?? '');
            $leadSingle->email = ($request->email ?? '');
            $leadSingle->mob = ($request->mob ?? '');
            $leadSingle->gstno = ($request->gstno ?? '');
            $leadSingle->whatsapp = ($request->whatsapp ?? '');
            $leadSingle->company = ($request->company ?? '');
            $leadSingle->position = ($request->position ?? '');
            $leadSingle->industry = ($request->industry ?? '');
            $leadSingle->location = ($location ?? '');
            $leadSingle->website = ($request->website ?? '');
            $leadSingle->assigned = ($request->assigned ?? Auth::User()->name ?? '');
            $leadSingle->purpose = ($request->purpose ?? '');
            $leadSingle->values = ($request->value ?? '');
            $leadSingle->language = ($request->language ?? '');
            $leadSingle->poc = ($request->poc ?? '');
            $leadSingle->tags = ($request->tags ?? '');
            
            if((!empty($request->nxtDate) && (new DateTime($request->nxtDate) > new DateTime())) || !empty($request->message)){
                $leadSingle->status = ($request->status ?? '1');
            }else{
                $leadSingle->status = ($request->status ?? '0');
            }
    
            if($leadSingle->save()) {
                
                if((!empty($request->nxtDate) && (new DateTime($request->nxtDate) > new DateTime())) || !empty($request->message)){
                    $leadComment = new Lead_comments();
            
                    $leadComment->lead_id = ($leadSingle->id ?? '');
                    $leadComment->msg = ($request->message ?? 'Call back at next date');
                    $leadComment->next_date = ($request->nxtDate ?? null);
                    
                    $leadComment->save();
                }
                
                return redirect('leads?page='.$currentPage)->with('success', 'This Lead was successfully added to the Leads Table.');
            } else {
                return redirect('leads?page='.$currentPage)->with('error', 'Failed to add this lead to the leads table.');
            }
            
        } else {
            
            // Updating an existing lead
            $id = $request->id ?? '';
            
            if(($request->status ?? '') == '5'){
                
                $leadSingle = Leads::find($id);
                
                // Creating a new lead
                $client = new Clients();
        
                $client->cid = (Auth::user()->cid ?? '');
                $client->commentLeadID = ($id ?? '');
                $client->name = ($request->name ?? '');
                $client->email = ($request->email ?? '');
                $client->mob = ($request->mob ?? '');
                $client->gstno = ($request->gstno ?? '');
                $client->whatsapp = ($request->whatsapp ?? '');
                $client->company = ($request->company ?? '');
                $client->position = ($request->position ?? '');
                $client->industry = ($request->industry ?? '');
                $client->location = ($location ?? '');
                $client->website = ($request->website ?? '');
                /*$client->assigned = ($request->assigned ?? '');*/
                $client->purpose = ($request->purpose ?? '');
                $client->values = ($request->value ?? '');
                $client->language = ($request->language ?? '');
                $client->poc = ($request->poc ?? '');
                $client->tags = ($request->tags ?? '');
                $client->status = '0';
        
                if($client->save()) {
                    
                    $proposal = Proposals::where('lead_id', $id)->first(); // get the proposal model
                    
                    if ($proposal) {
                        $proposal->lead_id = $client->id;
                        $proposal->related = 2;
                        $proposal->save();
                    }
                    
                    $leadSingle->delete();
                    
                    
                    return redirect('leads?page='.$currentPage)->with('success', "Successfully converted leads moved to client list.");
                } else {
                    return redirect('leads?page='.$currentPage)->with('error', 'Failed to add this lead to the client list.');
                }
                
            }else{
        
                $leadSingle = Leads::find($id);
                
                if(!$leadSingle) {
                    return back()->with('error', 'Lead not found.');
                }
        
                $leadSingle->cid = (Auth::user()->cid ?? '');
                $leadSingle->name = ($request->name ?? '');
                $leadSingle->email = ($request->email ?? '');
                $leadSingle->mob = ($request->mob ?? '');
                $leadSingle->gstno = ($request->gstno ?? '');
                $leadSingle->whatsapp = ($request->whatsapp ?? '');
                $leadSingle->company = ($request->company ?? '');
                $leadSingle->position = ($request->position ?? '');
                $leadSingle->industry = ($request->industry ?? '');
                $leadSingle->location = ($location ?? '');
                $leadSingle->website = ($request->website ?? '');
                $leadSingle->assigned = ($request->assigned ?? '');
                $leadSingle->purpose = ($request->purpose ?? '');
                $leadSingle->values = ($request->value ?? '');
                $leadSingle->language = ($request->language ?? '');
                $leadSingle->poc = ($request->poc ?? '');
                $leadSingle->tags = ($request->tags ?? '');
                $leadSingle->status = ($request->status ?? '10');
        
                if($leadSingle->update()) {
                    return redirect('leads?page='.$currentPage)->with('success', 'This Lead was successfully updated in the Leads Table.');
                } else {
                    return redirect('leads?page='.$currentPage)->with('error', 'Failed to update this lead in the leads table.');
                }
                
            }
        }
    }
    
    public function manageLeadCommentPost(Request $request) 
    {
        
        $currentPage = $request->page ?? 1;
        
        if(empty($request->id)) {
            
            $leadId = $request->lead_id ?? null;
            $clientId = $request->client_id ?? null;
            
            // Creating a new lead comment
            $leadComment = new Lead_comments();
            
            $leadComment->lead_id = $leadId;
            $leadComment->msg = $request->message;
            $leadComment->next_date = $request->nxtDate ?? null;
            
            if($leadComment->save()) {
                
                if(!empty($leadId)){
                    
                    $leadSingle = Leads::find($leadId);
                    
                    $leadSingle->status = '1';
                    $leadSingle->update();
                    
                    return redirect('leads?page='.$currentPage)->with('success', 'This comment was successfully added to the Leads Table.');
                    
                }else{
                    return redirect('clients?page='.$currentPage)->with('success', 'This comment was successfully added to the client Table.');
                }
            } else {
                if(!empty($leadId)){
                    return redirect('leads?page='.$currentPage)->with('error', 'Failed to add this comment to the leads table.');
                }else{
                    return redirect('clients?page='.$currentPage)->with('error', 'Failed to add this comment to the clients table.');
                }
            }
            
        } else {
            // Updating an existing lead comment
            $id = $request->id ?? '';
    
            $leadComment = Lead_comments::find($id);
            
            if(!$leadComment) {
                return redirect('leads?page='.$currentPage)->with('error', 'Lead comment not found.');
            }
    
            $leadComment->msg = $request->message;
            $leadComment->next_date = $request->nxtDate ?? null;
            
            
            $leadId = $request->lead_id ?? $leadComment->lead_id;
    
            if($leadComment->update()) {
                
                $leadSingle = Leads::find($leadId);
                
                $leadSingle->status = '1';
                $leadSingle->update();
                
                return redirect('leads?page='.$currentPage)->with('success', 'This lead comment was successfully updated in the Leads Table.');
            } else {
                return redirect('leads?page='.$currentPage)->with('error', 'Failed to update this lead comment in the leads table.');
            }
        }
    }
    
    public function proposals()
    {
        // Fetch proposals with dynamic joins and select based on 'related' field
        $query = Proposals::leftJoin('users', 'proposals.uid', '=', 'users.id')
                          ->leftJoin('leads', 'proposals.lead_id', '=', 'leads.id')
                          ->leftJoin('clients', 'proposals.lead_id', '=', 'clients.id');
    
        // Conditional select based on 'related' status
        $query->selectRaw("
            CASE 
                WHEN proposals.related = 1 THEN leads.name
                ELSE clients.name
            END AS lead_name,
            CASE 
                WHEN proposals.related = 1 THEN NULL
                ELSE clients.company
            END AS company,
            proposals.*
        ");
    
        // Apply filter for current user's company ID
        $query->where('users.cid', '=', Auth::user()->cid)
              ->orderBy('proposals.id', 'DESC');
    
        // Get results
        $proposals = $query->get();
    
        return view('proposals', ['proposals' => $proposals]);
    }
    
    public function manageProposal(Request $request) 
    {
        $id = $request->id ?? null; // or just $request->id
    
        // If there's an ID, load one proposal
        if ($id) {
            // `first()` returns a single model or null (not a collection).
            $proposal = Proposals::where('id', $id)->first();
            // Alternatively: $proposal = Proposals::find($id);
            
            // Get items for that single invoice
            $proposalItems = Proposal_items::where('proposal_id', $id)->get();
        } else {
            // No ID means we're creating a NEW invoice
            // You can create a blank model or set $invoice = null
            $proposal = null;
            // No items for a new invoice
            $proposalItems = collect(); 
        }
    
        $leads = Leads::where('cid', '=', Auth::User()->cid)->where('name', '!=', '')->orderBy('name','ASC')->get();
    
        $clients = Clients::where('cid', '=', Auth::User()->cid)->where('name', '!=', '')->orderBy('name','ASC')->get();
    
        $companies = Companies::where('id', '=', Auth::User()->cid)->first();
    
        return view('manageProposal', [
            'proposal'      => $proposal,
            'proposalItems' => $proposalItems,
            'leads'      => $leads,
            'clients'      => $clients,
            'companies'      => $companies,
        ]);
    }
    
    public function manageProposalPost(Request $request)
    {
        // 1) Validate main proposal fields
        $validatedData = $request->validate([
            'lead_id'               => 'required|integer|exists:leads,id',
            'subject'               => 'required|string|max:255',
            'related'               => 'nullable|string|max:255',
            'proposal_date'         => 'required|date',
            'open_till'             => 'nullable|date',
            'currency'              => 'nullable|string|max:10',
            'discount_type'         => 'nullable|in:none,before-tax,after-tax',
            'discount_percentage'   => 'nullable|numeric|min:0',
            'notes'                 => 'nullable|string',
        
            'client_name'           => 'required|string|max:255',
            'client_email'          => 'nullable|email|max:255',
            'client_phone'          => 'nullable|string|max:20',
            'client_address'        => 'nullable|string',
            'client_city'           => 'nullable|string|max:100',
            'client_state'          => 'nullable|string|max:100',
            'client_zip'            => 'nullable|string|max:20',
            'client_country'          => 'nullable|string|max:100',
        
            'sub_total'             => 'nullable|string',  // Allow string to handle formatted amounts
            'discount_amount_calculated' => 'nullable|string',
            'cgst_total'             => 'nullable|string',
            'sgst_total'             => 'nullable|string',
            'igst_total'             => 'nullable|string',
            'vat_total'             => 'nullable|string',
            'adjustment_amount'     => 'nullable|numeric',
            'grand_total'           => 'nullable|string',
            'status'                => 'nullable|string|in:draft,sent,accepted,rejected',
        
            'id'                    => 'nullable|integer|exists:proposals,id'
        ]);
    
        // 2) Convert string amounts to numeric values (strip ₹ and parse as float)
        $subTotal = $this->convertCurrencyStringToNumber($validatedData['sub_total'] ?? '0');
        $discountAmountCalculated = $this->convertCurrencyStringToNumber($validatedData['discount_amount_calculated'] ?? '0');
        $cgst_total = $this->convertCurrencyStringToNumber($validatedData['cgst_total'] ?? '0');
        $sgst_total = $this->convertCurrencyStringToNumber($validatedData['sgst_total'] ?? '0');
        $igst_total = $this->convertCurrencyStringToNumber($validatedData['igst_total'] ?? '0');
        $vat_total = $this->convertCurrencyStringToNumber($validatedData['vat_total'] ?? '0');
        $grandTotal = $this->convertCurrencyStringToNumber($validatedData['grand_total'] ?? '0');
    
        // 3) Determine if this is an update or create action
        if (!empty($validatedData['id'])) {
            $proposal = Proposals::findOrFail($validatedData['id']);
        } else {
            $proposal = new Proposals();
            $proposal->uid                = Auth::User()->id ?? null;
        }
    
        // 4) Assign values
        $proposal->lead_id                = $validatedData['lead_id'];
        $proposal->subject                = $validatedData['subject'];
        $proposal->related                = $validatedData['related'] ?? 1;
        $proposal->proposal_date          = $validatedData['proposal_date'];
        $proposal->open_till              = $validatedData['open_till'] ?? null;
        $proposal->currency               = $validatedData['currency'] ?? 'USD';
        $proposal->discount_type          = $validatedData['discount_type'] ?? 'none';
        $proposal->discount_percentage    = $validatedData['discount_percentage'] ?? 0;
        $proposal->notes                  = $validatedData['notes'] ?? null;
    
        $proposal->client_name            = $validatedData['client_name'];
        $proposal->client_email           = $validatedData['client_email'] ?? null;
        $proposal->client_phone           = $validatedData['client_phone'] ?? null;
        $proposal->client_address         = $validatedData['client_address'] ?? null;
        $proposal->client_city            = $validatedData['client_city'] ?? null;
        $proposal->client_state           = $validatedData['client_state'] ?? null;
        $proposal->client_zip             = $validatedData['client_zip'] ?? null;
        $proposal->client_country         = $validatedData['client_country'] ?? null;
    
        // Store numeric values after conversion
        $proposal->sub_total              = $subTotal;
        $proposal->discount_amount_calculated = $discountAmountCalculated;
        $proposal->cgst_total              = $cgst_total;
        $proposal->sgst_total              = $sgst_total;
        $proposal->igst_total              = $igst_total;
        $proposal->vat_total              = $vat_total;
        $proposal->adjustment_amount      = $validatedData['adjustment_amount'] ?? 0;
        $proposal->grand_total            = $grandTotal;
        if($request->submit == 'Save & Send'){
        $proposal->status                 = $validatedData['status'] ?? 'Sent';
        }else{
        $proposal->status                 = $validatedData['status'] ?? 'draft';
        }
    
        // 5) Save to get ID
        $proposal->save();
        
        // 6) Handle proposal items
        if ($request->has('proposal_items')) {
            if (!empty($validatedData['id'])) {
                Proposal_items::where('proposal_id', $proposal->id)->delete();
            }
    
            foreach ($request->input('proposal_items', []) as $row) {

                $itemName   = $row['item_name']    ?? '';
                $descr      = $row['description']  ?? '';
                $qty        = !empty($row['quantity']) ? (int) $row['quantity'] : 1;
                $rate       = !empty($row['rate'])     ? (float) $row['rate']    : 0;
            
                // Skip completely blank rows
                if (!$itemName && !$descr && $qty <= 0 && $rate <= 0) {
                    continue;
                }
            
                // ---------- 2.  Parse selected taxes ----------
                $cgst = $sgst = $igst = $vat = 0.0;        // defaults
                $taxAmountTotal = 0.0;
            
                foreach ($row['tax_percentage'] ?? [] as $entry) {
                    [$code, $percent] = explode(':', $entry);
                    $percent = (float) $percent;
            
                    // store individual component
                    switch (strtolower($code)) {
                        case '0': $cgst = $percent; break;
                        case '1': $sgst = $percent; break;
                        case '2': $igst = $percent; break;
                        case '3':  $vat  = $percent; break;
                    }
            
                    // accumulate this tax into the line total
                    $taxAmountTotal += ($rate * $qty) * ($percent / 100);
                }
            
                // ---------- 3.  Grand total for this line ----------
                $lineSubtotal = $rate * $qty;           // price before any tax
                $lineTotal    = $lineSubtotal + $taxAmountTotal;
            
                $proposalItem = new Proposal_items();
                $proposalItem->proposal_id       = $proposal->id;
                $proposalItem->item_name         = $itemName;
                $proposalItem->description       = $descr;
                $proposalItem->quantity          = $qty;
                $proposalItem->rate              = $rate;
                $proposalItem->cgst_percent      = $cgst;
                $proposalItem->sgst_percent      = $sgst;
                $proposalItem->igst_percent      = $igst;
                $proposalItem->vat_percent       = $vat;
                $proposalItem->item_total_amount = $lineTotal;
                $proposalItem->save();
            }
            
        }
        
        
        if($request->submit == 'Save & Send'){
            
            $to = $validatedData['client_email'] ?? '';
            $subject = 'Business Proposal #000'.($proposal->id ?? '').' Received: '.($validatedData['subject']);
            
            $message = "
            We have also attached our business proposal for your kind perusal.<br><br>
            
            <b>Proposal ID:</b> #000".($proposal->id ?? '')."<br>
            <b>Valid Until:</b> ".(date_format(date_create($proposal->open_till ?? null), 'd M, Y'))."<br>
            You can view the full proposal at the following link: <a href='https://esecrm.com/proposal/".($proposal->id ?? '')."/".md5($proposal->client_email ?? '')."'>View Proposal</a><br><br>
            
            If you have any questions or comments, feel free to reach out or comment online. We are here to assist you.<br><br>
            
            Thank you once again for your interest and trust.<br><br>
            ";

            $viewName = 'emails.proposal';
            $company = session('companies');
            $signature = nl2br(Auth::User()->esign) ?? "Regards<br>Webbrella Global";
            $viewData = ["name" => ($validatedData['client_name'] ?? 'Sir/Mam'), "messages" => $message, "company" => ($company->name ?? ''), "signature" => $signature];
    
            // 1. Try to find user-specific settings
            $smtpSettings = SmtpSettings::where('user_id', Auth::id())->first();
            
            // 2. Fallback to company-specific settings (if no user-specific found and user has a cid)
            if (!$smtpSettings && Auth::user()->cid) {
                $smtpSettings = SmtpSettings::where('cid', Auth::user()->cid)->first();
            }


            $fromAddress = $smtpSettings?->from_address; // Get from DB if available
            $fromName = $smtpSettings?->from_name;       // Get from DB if available
            
            $mailable = new CustomMailable(
                $subject,
                $viewName,
                $viewData,
                $fromAddress, // Pass DB value or null
                $fromName     // Pass DB value or null
            );
    
            Mail::to($to)->send($mailable);
            //Mail::to($to)->send(new CustomMailable($subject, $viewName, $viewData));
            
            return back()->with('success', 'Proposal sent successfully!');
        }
        
        // 7) Redirect or response
        return back()->with('success', 'Proposal saved successfully!');
    }
    
    // Helper function to convert currency strings like "₹100.00" to float
    private function convertCurrencyStringToNumber($currencyString)
    {
        // Remove the currency symbol (₹) and convert to float
        $currencyString = preg_replace('/[^0-9.]+/', '', $currencyString); // Remove non-numeric chars
        return (float)$currencyString;
    }
    
    public function proposal($id, $token)
    {
        $proposal = Proposals::leftJoin('leads','proposals.lead_id','=','leads.id')
            ->leftJoin('companies','leads.cid','=','companies.id')
            ->select(
                'leads.name as lead_name',
                'companies.name as companyName',
                'companies.email as companyEmail',
                'companies.mob as companyMob',
                'companies.gst as gst',
                'companies.vat as vat',
                'companies.img as companyImg',
                'companies.address as companyAddress',
                'companies.city as companyCity',
                'companies.state as companyState',
                'companies.zipcode as companyZipCode',
                'companies.country as companyCountry',
                'proposals.*'
            )
            ->where('proposals.id', $id)
            ->first();
            
        $proposalItems = Proposal_items::where('proposal_id', ($proposal->id ?? ''))->get();

        if (md5($proposal->client_email) !== $token) {
            abort(403, 'Unauthorized or invalid token.');
        }

        return view('viewProposal', ['proposal' => $proposal,'proposalItems' => $proposalItems]);
    
    }
    
    public function downloadPdf($id, $token)
    {
        try {
            $proposal = Proposals::leftJoin('leads', 'proposals.lead_id', '=', 'leads.id')
                ->leftJoin('companies', 'leads.cid', '=', 'companies.id')
                ->select(
                    'leads.name as lead_name',
                    'companies.name as companyName',
                    'companies.email as companyEmail',
                    'companies.mob as companyMob',
                    'companies.gst as gst',
                    'companies.vat as vat',
                    'companies.img as companyImg',
                    'companies.address as companyAddress',
                    'companies.city as companyCity',
                    'companies.state as companyState',
                    'companies.zipcode as companyZipCode',
                    'companies.country as companyCountry',
                    'proposals.*'
                )
                ->where('proposals.id', $id)
                ->firstOrFail();
    
            $proposalItems = Proposal_items::where('proposal_id', $proposal->id)->get();
    
            if (md5($proposal->client_email) !== $token) {
                abort(403, 'Unauthorized or invalid token.');
            }
    
            // Create PDF with the same template used for preview
            $pdf = Pdf::loadView('proposals.pdf_template', [
                'proposal' => $proposal,
                'proposalItems' => $proposalItems,
            ])->setPaper('a4', 'portrait');
    
            $filename = "Proposal-{$proposal->id}-" . Str::slug($proposal->subject ?? 'proposal') . ".pdf";
    
            return $pdf->download($filename);
    
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Proposal not found.');
        } catch (\Exception $e) {
            abort(500, 'Could not generate PDF.');
        }
    }
    
    public function declineProposal($id, $tocken)
    {
        $proposal = Proposals::findOrFail($id);
    
        $user = User::where('id', ($proposal->uid ?? ''))->first();
        
        $to = $user->email ?? '';
        $subject = 'Business Proposal #000' . $proposal->id . ' Declined: ' . ($proposal->subject ?? '');
        $clientName = $proposal->client_name ?? 'Sir/Mam';
    
        $message = "
            We have also attached our business proposal for your kind perusal.<br><br>
            <b>Proposal ID:</b> #000{$proposal->id}<br>
            <b>Valid Until:</b> " . ($proposal->open_till ? date('d M, Y', strtotime($proposal->open_till)) : '-') . "<br>
            You can view the full proposal at the following link: 
            <a href='https://esecrm.com/proposal/{$proposal->id}/" . md5($proposal->client_email) . "'>View Proposal</a><br><br>
            If you have any questions or comments, feel free to reach out or comment online. We are here to assist you.<br><br>
            Thank you once again for your interest and trust.<br><br>
        ";
    
        $viewName = 'emails.proposal';
        $company = session('companies');
        $signature = nl2br($user->esign ?? "Regards<br>Webbrella Global");
    
        $viewData = [
            "name" => $clientName,
            "messages" => $message,
            "company" => $company->name ?? '',
            "signature" => $signature
        ];
    
        $smtpSettings = SmtpSettings::where('user_id', $user->id)->first();
        if (!$smtpSettings && $user->cid) {
            $smtpSettings = SmtpSettings::where('cid', $user->cid)->first();
        }
    
        $fromAddress = $smtpSettings?->from_address;
        $fromName = $smtpSettings?->from_name;
    
        $mailable = new CustomMailable(
            $subject,
            $viewName,
            $viewData,
            $fromAddress,
            $fromName
        );
        
        $proposal->status = 'Declined';
        $proposal->save();
    
        Mail::to($to)->send($mailable);
    
        return back()->with('success', 'Proposal declined and email sent successfully!');
    }

    public function acceptProposal(Request $request, $id, $token)
    {
        $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|max:100',
            'signature_data' => 'required|string',
        ]);
    
        // Decode base64 image
        $signatureData = $request->input('signature_data');
        $image = str_replace('data:image/png;base64,', '', $signatureData);
        $image = str_replace(' ', '+', $image);
        $fileName = 'signature_' . time() . '.png';
    
        // Save to /public/assets/images/signs/
        $path = public_path("assets/images/signs/{$fileName}");
        file_put_contents($path, base64_decode($image));
    
        // Save to database
        Proposal_signatures::create([
            'proposal_id' => $id,
            'token' => $token,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'signature_path' => "assets/images/signs/{$fileName}", // for later public use
        ]);
        
        $proposal = Proposals::findOrFail($id);
        
        $user = User::where('id', ($proposal->uid ?? ''))->first();
        
        $to = $user->email ?? '';
        $subject = 'Business Proposal #000' . $proposal->id . ' Accepted: ' . ($proposal->subject ?? '');
        $clientName = $proposal->client_name ?? 'Sir/Mam';
        
        $message = "
            We are pleased to inform you that your business proposal has been accepted.<br><br>
            <b>Proposal ID:</b> #000{$proposal->id}<br>
            <b>Valid Until:</b> " . ($proposal->open_till ? date('d M, Y', strtotime($proposal->open_till)) : '-') . "<br>
            You can view the full proposal at the following link: 
            <a href='https://esecrm.com/proposal/{$proposal->id}/" . md5($proposal->client_email) . "'>View Proposal</a><br><br>
            We look forward to working together and building a successful collaboration.<br><br>
            If you have any questions or suggestions, feel free to reach out to us.<br><br>
            Thank you for your trust and confidence in our company.<br><br>
        ";
        
        $viewName = 'emails.proposal';
        $company = session('companies');
        $signature = nl2br($user->esign ?? "Regards<br>Webbrella Global");
        
        $viewData = [
            "name" => $clientName,
            "messages" => $message,
            "company" => $company->name ?? '',
            "signature" => $signature
        ];
        
        // Load SMTP Settings
        $smtpSettings = SmtpSettings::where('user_id', $user->id)->first();
        if (!$smtpSettings && $user->cid) {
            $smtpSettings = SmtpSettings::where('cid', $user->cid)->first();
        }
        
        $fromAddress = $smtpSettings?->from_address;
        $fromName = $smtpSettings?->from_name;
        
        // Send Email
        $mailable = new CustomMailable(
            $subject,
            $viewName,
            $viewData,
            $fromAddress,
            $fromName
        );
        
        Mail::to($to)->send($mailable);

        $proposal->status = 'Accepted';
        $proposal->save();
    
        return redirect()->back()->with('success', 'Signature submitted successfully.');
    }

    /*Lead Assign Controller*/
    public function leadsPost(Request $request)
    {
        
        if(Auth::user()->role == '0'){
            
            $leads = Leads::leftJoin('lead_comments', function($join) {
                $join->on('leads.id', '=', 'lead_comments.lead_id')
                    ->whereIn('lead_comments.next_date', function ($query) {
                        $query->select(DB::raw('MAX(next_date)'))
                           ->from('lead_comments')
                           ->whereColumn('lead_comments.lead_id', 'leads.id');
                    });
            })
            ->select(
                'leads.id', 
                'leads.cid', 
                'leads.name', 
                'leads.company', 
                'leads.email', 
                'leads.mob', 
                'leads.whatsapp', 
                'leads.location', 
                'leads.purpose', 
                'leads.assigned',   
                'leads.values',
                'leads.poc', 
                'leads.status', 
                'leads.created_at', 
                'leads.updated_at', 
                DB::raw('MAX(lead_comments.next_date) as next_date'), // Get the max next_date
                'lead_comments.created_at as last_talk' // Get the msg field
            )
            ->groupBy(
                'leads.id', 
                'leads.cid', 
                'leads.name', 
                'leads.company', 
                'leads.email', 
                'leads.mob', 
                'leads.whatsapp', 
                'leads.location', 
                'leads.purpose', 
                'leads.assigned',   
                'leads.values',
                'leads.poc', 
                'leads.status', 
                'leads.created_at', 
                'leads.updated_at',
                'lead_comments.created_at' // Add msg to groupBy
            )
            ->orderByRaw('
                CASE 
                    WHEN DATE(lead_comments.next_date) <= CURDATE() THEN 0
                    ELSE 1
                END ASC
            ')
            ->orderBy('leads.status', 'ASC')
            ->orderBy('leads.created_at', 'DESC')
            ->get();
            
        }else{
            
            $leads = Leads::leftJoin('lead_comments', function($join) {
                $join->on('leads.id', '=', 'lead_comments.lead_id')
                    ->whereIn('lead_comments.next_date', function ($query) {
                        $query->select(DB::raw('MAX(next_date)'))
                           ->from('lead_comments')
                           ->whereColumn('lead_comments.lead_id', 'leads.id');
                    });
            })
            ->select(
                'leads.id', 
                'leads.cid', 
                'leads.name', 
                'leads.company', 
                'leads.email', 
                'leads.mob', 
                'leads.whatsapp', 
                'leads.location', 
                'leads.purpose', 
                'leads.assigned',   
                'leads.values',
                'leads.poc', 
                'leads.status', 
                'leads.created_at', 
                'leads.updated_at', 
                DB::raw('MAX(lead_comments.next_date) as next_date'), // Get the max next_date
                'lead_comments.msg' // Get the msg field
            )
            ->where('leads.cid', '=', Auth::user()->cid)
            ->groupBy(
                'leads.id', 
                'leads.cid', 
                'leads.name', 
                'leads.company', 
                'leads.email', 
                'leads.mob', 
                'leads.whatsapp', 
                'leads.location', 
                'leads.purpose', 
                'leads.assigned',   
                'leads.values',
                'leads.poc', 
                'leads.status', 
                'leads.created_at', 
                'leads.updated_at',
                'lead_comments.msg' // Add msg to groupBy
            )
            ->orderByRaw('
                CASE 
                    WHEN DATE(lead_comments.next_date) <= CURDATE() THEN 0
                    ELSE 1
                END ASC
            ')
            ->orderBy('leads.status', 'ASC')
            ->orderBy('leads.created_at', 'DESC')
            ->get();

        }

        return view('leads',['leads'=>$leads]);
        
    }
    
    public function reminderScript()
    {
        try {
            // Fetch leads with reminders
            $leads = DB::table('leads')
                ->leftJoin('lead_comments', function ($join) {
                    $join->on('leads.id', '=', 'lead_comments.lead_id')
                        ->whereIn('lead_comments.next_date', function ($query) {
                            $query->select(DB::raw('MAX(next_date)'))
                                ->from('lead_comments')
                                ->whereColumn('lead_comments.lead_id', 'leads.id');
                        });
                })
                ->select(
                    'leads.id',
                    'leads.cid',
                    'leads.name',
                    'leads.company',
                    'leads.email',
                    'leads.mob',
                    'leads.whatsapp',
                    'leads.location',
                    'leads.purpose',
                    'leads.assigned',
                    'leads.values',
                    'leads.poc',
                    'leads.status',
                    'leads.created_at',
                    'leads.updated_at',
                    'lead_comments.msg',
                    DB::raw('MAX(lead_comments.next_date) as next_date'),
                    DB::raw('MAX(lead_comments.created_at) as last_talk')
                )
                ->groupBy(
                    'leads.id',
                    'leads.cid',
                    'leads.name',
                    'leads.company',
                    'leads.email',
                    'leads.mob',
                    'leads.whatsapp',
                    'leads.location',
                    'leads.purpose',
                    'leads.assigned',
                    'leads.values',
                    'leads.poc',
                    'leads.status',
                    'leads.created_at',
                    'leads.updated_at',
                    'lead_comments.msg'
                )
                ->orderByRaw('
                    CASE 
                        WHEN leads.status = 1 AND DATE(MAX(lead_comments.next_date)) <= CURDATE() THEN 0
                        ELSE 1
                    END ASC
                ')
                ->orderBy('leads.status', 'ASC')
                ->orderBy('leads.created_at', 'DESC')
                ->get();

            foreach ($leads as $lead) {
                if ($lead->next_date && Carbon::parse($lead->next_date)->isToday()) {
                    // Prepare the notification message with a clickable link
                    $notificationLink = "https://esecrm.com/leads"; // Replace with your actual lead detail URL  Click here to view details: {$notificationLink}
                    $message = "Reminder for Lead: {$lead->name}. Message: {$lead->msg}.";
            
                    // Prepare the API URL
                    $url = "https://esecrm.com/api/v1/send-notification?" . http_build_query([
                        'title' => 'ESECRM',
                        'msg'   => $message,
                        'url' => $notificationLink,
                        'mono'  => "msetah@gmail.com",
                    ]);
            
                    // Send the notification
                    $response = $this->sendNotification($url);
            
                    // Log the result
                    Log::info("Notification sent to {$lead->email}: {$response}");
                }
            }

        } catch (\Exception $e) {
            // Handle exceptions
            Log::error('Error in reminder script: ' . $e->getMessage());
        }
    }
    
    protected function sendNotification($url)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $error = "cURL Error: " . curl_error($curl);
            curl_close($curl);
            throw new \Exception($error);
        }

        curl_close($curl);

        return $response;
    }
    
    public function importLeads(Request $request)
    {
        // Counters and trackers for success/fail
        $uploadedCount = 0;
        $notUploadedCount = 0;
        $notUploadedRows = []; // Store row numbers (or keys) that fail
    
        try {
            // Open the file and read its contents
            if (($handle = fopen($request->file('impLeadFile')->getRealPath(), 'r')) !== FALSE) {
                // Skip the header row
                fgetcsv($handle);
    
                $rowIndex = 1;  // Keep track of row index (start from 1 after header)
    
                // Loop through the CSV rows
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    /*
                     |------------------------------------------------------------------------------
                     | 1. Check required fields (name, mob, status)
                     |    Determine the "status" from data[14] if set:
                     |     - 'converted' => special handling
                     |     - 'lost' => 9
                     |     - else => 0
                     |------------------------------------------------------------------------------
                     */
                    $name   = $data[0] ?? null;
                    $name = mb_convert_encoding($name, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
                    $name = mb_substr($name, 0, 230);
                    $company   = $data[4] ?? null;
                    $company = mb_convert_encoding($company, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
                    $location = json_encode(explode(',',($data[7])));
                    $mob    = $data[2] ?? null;
                    $status = 0; // default
    
                    // If there's a 14th index, interpret its value
                    if (!empty($data[14])) {
                        if ($data[14] === 'lost') {
                            $status = 9;
                        } elseif ($data[14] === 'converted') {
                            // We'll handle the 'converted' logic below, but just note it here
                            $status = 0;  // or whatever you want as default for converted
                        } else {
                            $status = 0; // default for everything else
                        }
                    }
    
                    // If required fields are missing, skip row
                    // Here, we interpret "status" as we derived above, 
                    // but the presence of a name and mob is strictly required
                    if (empty($name) || empty($mob)) {
                        $notUploadedCount++;
                        $notUploadedRows[] = $rowIndex;
                        $rowIndex++;
                        continue; 
                    }
    
                    // Parse date fields using Carbon (optional fields)
                    // We'll safely check if index is set or not
                    try {
                        $last_talk  = isset($data[15]) ? Carbon::parse($data[15])->format('Y-m-d H:i:s') : now();
                        $created_at = isset($data[16]) ? Carbon::parse($data[16])->format('Y-m-d H:i:s') : now();
                        $reminder   = isset($data[17]) ? Carbon::parse($data[17])->format('Y-m-d H:i:s') : now();
                    } catch (\Exception $e) {
                        // Fallback if date parsing fails
                        $last_talk  = now();
                        $created_at = now();
                        $reminder   = now();
                    }
    
                    /*
                     |------------------------------------------------------------------------------
                     | 2. Handle "converted" => Insert into `clients` table
                     |    Otherwise, insert/update in `leads` table
                     |------------------------------------------------------------------------------
                     */
                    if (!empty($data[14]) && $data[14] === 'converted') {
                        // 2A. If lead is converted, insert/update in 'clients'
                        $existingClient = DB::table('clients')
                            ->where('email', '=', ($data[1] ?? ''))
                            ->orWhere('mob', '=', ($data[2] ?? ''))
                            ->first();
    
                        if ($existingClient) {
                            // Update existing client
                            DB::table('clients')
                                ->where('id', $existingClient->id)
                                ->update([
                                    'name'       => $name,
                                    'email'      => $data[1] ?? '',
                                    'mob'        => '+91' . $mob,
                                    'whatsapp'   => $data[3] ?? '',
                                    'company'    => $company ?? '',
                                    'position'   => $data[5] ?? '',
                                    'industry'   => $data[6] ?? '',
                                    'location'   => $location ?? '',
                                    'website'    => $data[8] ?? '',
                                    'assigned'     => $data[9] ?? '',
                                    'purpose'    => $data[10] ?? '',
                                    'values'     => $data[11] ?? '',
                                    'language'   => $data[12] ?? '',
                                    'poc'        => $data[13] ?? '',
                                    'status'     => '0', // up to you how you define "converted"
                                    'updated_at' => now(),
                                ]);
                        } else {
                            // Insert new client
                            DB::table('clients')->insert([
                                'cid'           => Auth::user()->cid ?? '',
                                'commentLeadID' => 0,
                                'name'          => $name,
                                'email'         => $data[1] ?? '',
                                'mob'           => $mob,
                                'whatsapp'      => $data[3] ?? '',
                                'company'       => $company ?? '',
                                'position'      => $data[5] ?? '',
                                'industry'      => $data[6] ?? '',
                                'location'      => $location ?? '',
                                'website'       => $data[8] ?? '',
                                'assigned'        => $data[9] ?? '',
                                'purpose'       => $data[10] ?? '',
                                'values'        => $data[11] ?? '',
                                'language'      => $data[12] ?? '',
                                'poc'           => $data[13] ?? '',
                                'status'        => '0',
                                'created_at'    => $created_at,
                            ]);
                        }
                    } else {
                        // 2B. Insert/Update "leads" table
                        $checkLeads = Leads::where('mob', '=', ($mob))->first();
    
                        if ($checkLeads) {
                            // Update the existing lead
                            $checkLeads->update([
                                'cid'       => Auth::user()->cid ?? '',
                                'name'      => $name,
                                'email'     => $data[1] ?? '',
                                'mob'       => $mob,
                                'whatsapp'  => $data[3] ?? '',
                                'company'   => $company ?? '',
                                'position'  => $data[5] ?? '',
                                'industry'  => $data[6] ?? '',
                                'location'  => $location ?? '',
                                'website'   => $data[8] ?? '',
                                'assigned'    => $data[9] ?? '',
                                'purpose'   => $data[10] ?? '',
                                'values'    => $data[11] ?? '',
                                'language'  => $data[12] ?? '',
                                'poc'       => $data[13] ?? '',
                                'status'    => $status,
                                'updated_at'=> now(),
                            ]);
    
                            // Insert comment if last_talk is provided
                            if (!empty($data[15])) {
                                DB::table('lead_comments')->insert([
                                    'lead_id'    => $checkLeads->id,
                                    'msg'        => $data[18] ?? 'Updated Data',
                                    'next_date'  => $reminder,
                                    'created_at' => $last_talk,
                                ]);
                            }
                        } else {
                            // Insert new lead
                            $lead_id = DB::table('leads')->insertGetId([
                                'cid'        => Auth::user()->cid ?? '',
                                'name'       => $name,
                                'email'      => $data[1] ?? '',
                                'mob'        => $mob,
                                'whatsapp'   => $data[3] ?? '',
                                'company'    => $company ?? '',
                                'position'   => $data[5] ?? '',
                                'industry'   => $data[6] ?? '',
                                'location'   => $location ?? '',
                                'website'    => $data[8] ?? '',
                                'assigned'     => $data[9] ?? '',
                                'purpose'    => $data[10] ?? '',
                                'values'     => $data[11] ?? '',
                                'language'   => $data[12] ?? '',
                                'poc'        => $data[13] ?? '',
                                'status'     => $status,
                                'created_at' => $created_at,
                            ]);
    
                            // Add initial comment if last_talk is provided
                            if (!empty($data[15])) {
                                DB::table('lead_comments')->insert([
                                    'lead_id'    => $lead_id,
                                    'msg'        => $data[18] ?? 'Import Data',
                                    'next_date'  => $reminder,
                                    'created_at' => $last_talk,
                                ]);
                            }
                        }
                    }
    
                    // Successfully processed this row
                    $uploadedCount++;
                    $rowIndex++;
                }
    
                // Close the file after reading all rows
                fclose($handle);
    
                // Build a message with summary
                $summaryMessage = 'Data imported successfully! '
                    . 'Successfully Uploaded: ' . $uploadedCount . ' | '
                    . 'Not Uploaded: ' . $notUploadedCount;
    
                // Optionally show which row indexes failed
                if ($notUploadedCount > 0) {
                    $summaryMessage .= ' | Rows skipped: ' . implode(', ', $notUploadedRows);
                }
    
                return back()->with('success', $summaryMessage);
            } else {
                return back()->with('error', 'Could not open the file.');
            }
        } catch (Exception $e) {
            // Handle errors gracefully
            return back()->with('error', 'An error occurred during import: ' . $e->getMessage());
        }
    }
    
    public function exportLeads()
    {
        
        // Check if the user is logged in
        if (!Auth::check()) {
            // Redirect to the login page with an error message
            return redirect()->route('login')->with('error', 'Oops, something went wrong. Kindly log in to your account first, then export your file.');
        }
        
        // Set the headers for the CSV file download
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=leads.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );
    
        // Callback function to write the CSV content
        $callback = function() {
            // Open output buffer for writing the CSV data
            $file = fopen('php://output', 'w');
    
            // Write the column headers in the first row (matching the columns in the import)
            fputcsv($file, ['CID', 'Name', 'Email', 'Mobile', 'WhatsApp', 'Company', 'Position', 'Industry', 'Location', 'Website', 'assigned', 'Purpose', 'Values', 'Language', 'POC', 'Status', 'Last Talk Date', 'Created Date', 'Next Rimder Date', 'Note']);
    
            // Fetch data from the database
            $leads = DB::table('leads')
                ->select('leads.*', 'lc.msg', 'lc.next_date', 'lc.last_talk')
                ->leftJoin(DB::raw('(SELECT lead_id, msg, next_date, created_at as last_talk
                                    FROM lead_comments
                                    WHERE id IN (SELECT MAX(id) FROM lead_comments GROUP BY lead_id)
                                ) as lc'), 'leads.id', '=', 'lc.lead_id')
                ->where('leads.cid', '=', Auth::user()->cid)
                ->where('leads.status', '!=', '9')
                ->get();

            // Loop through the leads and write each row into the CSV
            foreach ($leads as $lead) {
                fputcsv($file, [
                    $lead->cid,
                    $lead->name,
                    $lead->email,
                    $lead->mob,
                    $lead->whatsapp,
                    $lead->company,
                    $lead->position,
                    $lead->industry,
                    $lead->location,
                    $lead->website,
                    $lead->assigned,
                    $lead->purpose,
                    $lead->values,
                    $lead->language,
                    $lead->poc,
                    $lead->status,
                    $lead->last_talk,
                    $lead->created_at,
                    $lead->next_date,
                    $lead->msg,
                ]);
            }
    
            // Close the file
            fclose($file);
        };
    
        // Return the response with headers and content generated from the callback
        return response()->stream($callback, 200, $headers);
    }
    
    public function exportAllLeads()
    {
        
        // Set the headers for the CSV file download
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=leads.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );
    
        // Callback function to write the CSV content
        $callback = function() {
            // Open output buffer for writing the CSV data
            $file = fopen('php://output', 'w');
    
            // Write the column headers in the first row (matching the columns in the import)
            fputcsv($file, ['CID', 'Name', 'Email', 'Mobile', 'WhatsApp', 'Company', 'Position', 'Industry', 'Location', 'Website', 'Assigned', 'Purpose', 'Values', 'Language', 'POC', 'Status']);
    
            // Fetch data from the database
            $leads = DB::table('leads')->get();
    
            // Loop through the leads and write each row into the CSV
            foreach ($leads as $lead) {
                fputcsv($file, [
                    $lead->cid,
                    $lead->name,
                    $lead->email,
                    $lead->mob,
                    $lead->whatsapp,
                    $lead->company,
                    $lead->position,
                    $lead->industry,
                    $lead->location,
                    $lead->website,
                    $lead->assigned,
                    $lead->purpose,
                    $lead->values,
                    $lead->language,
                    $lead->poc,
                    $lead->status,
                ]);
            }
    
            // Close the file
            fclose($file);
        };
    
        // Return the response with headers and content generated from the callback
        return response()->stream($callback, 200, $headers);
    }
}
