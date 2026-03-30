<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Mail\CustomMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\AuthController;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\SmtpSettings;
use App\Models\User;
use App\Models\Leads;
use App\Models\Clients;
use App\Models\Eselicenses;
use App\Models\Companies;
use App\Models\Contracts;
use App\Models\Projects;
use App\Models\Lead_comments;
use App\Models\Recoveries;
use App\Models\Invoices;
use App\Models\Invoice_items;
use App\Models\CustomerDepartments;

use App\Services\ClientService;

class ClientController extends Controller
{
    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }
    public function getProjects($clientId)
    {
        // Fetch projects for the given client ID
        $projects = Projects::where('client_id', $clientId)->select('id', 'name', 'amount')->get();

        // Return projects as JSON
        return response()->json(['projects' => $projects]);
    }

    public function clientList(Request $request)
    {
        $clients = Clients::select('id', 'name', 'company', 'email', 'mob', 'location')->where('cid', '=', Auth::user()->cid)->where('name', '!=', '')->orderBy('name', 'ASC')->get();

        return json_encode(['clients' => $clients]);
    }

    public function getClient($clientId)
    {
        $client = Clients::find($clientId);
        if ($client) {
            return response()->json([
                'client' => [
                    'batchNo' => $client->batchNo,
                    'name' => $client->name,
                    'company' => $client->company,
                    'mobile' => $client->mob,
                    'whatsapp' => $client->whatsapp,
                ]
            ]);
        } else {
            return response()->json(['client' => null]);
        }
    }

    public function recovery($id = null, $title = null)
    {

        if ($title == "Received") {
            // Fetch all recoveries for the given project ID
            $recoveries = Recoveries::where('project_id', $id)->where('paid', '!=', '0')->get();

            // Fetch project details
            $project = Projects::find($id); // More concise than where('id', $id)->first()

            // Calculate the total paid amount
            $totalPaid = Recoveries::where('project_id', $id)->sum('paid');
            $client = Clients::where('id', ($project->client_id ?? ''))->first();

            // Return the view with the recoveries data, project details, and total paid amount
            return view('inc.recovery.received', compact('recoveries', 'project', 'totalPaid', 'client'));
        } else {
            // Fetch all recoveries for the given project ID
            $recoveries = Recoveries::where('project_id', $id)->get();

            // Fetch project details
            $project = Projects::find($id); // More concise than where('id', $id)->first()

            // Calculate the total paid amount
            $totalPaid = Recoveries::where('project_id', $id)->sum('paid');
            $client = Clients::where('id', ($project->client_id ?? ''))->first();

            // Return the view with the recoveries data, project details, and total paid amount
            return view('inc.recovery.reminder', compact('recoveries', 'project', 'totalPaid', 'client'));
        }
    }

    public function recoveryPost(Request $request)
    {
        if ($this->clientService->recordRecovery($request->all())) {
            return redirect('recoveries')->with('success', 'Operation completed successfully.');
        }
        return back()->with('error', 'Failed to process recovery.');
    }

    public function recoveries()
    {
        $recoveries = $this->clientService->getRecoveriesSummary(Auth::user()->cid);
        $totalRemaining = $recoveries->sum('remaining_amount');

        return view('recoveries', ['totalRemaining' => $totalRemaining, 'recoveries' => $recoveries]);
    }

    public function manageRecovery(Request $request)
    {

        $recoveries = Recoveries::leftjoin('clients', 'recoveries.client_id', '=', 'clients.id')
            ->leftjoin('projects', 'recoveries.project_id', '=', 'projects.id')
            ->select('clients.batchNo', 'clients.name', 'clients.company', 'clients.mob', 'clients.whatsapp', 'clients.industry', 'clients.email', 'clients.poc', 'projects.name as project', 'projects.amount', 'projects.deployment_url', 'projects.note as msg', 'recoveries.*')
            ->where('recoveries.cid', '=', Auth::user()->cid)->where('projects.id', '=', ($request->id ?? ''))->first();

        $clients = Clients::get();

        $projects = Projects::where('id', '=', ($recoveries->project_id ?? ''))->get();

        return view('manageRecovery', ['recoveries' => $recoveries, 'clients' => $clients, 'projects' => $projects]);

    }

    public function updateRecoveryAmount(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
        ]);

        $record = Recoveries::find($request->id); // Replace 'Recovery' with your model
        if ($record) {
            $record->paid = $request->amount; // Update the amount
            $record->save();

            return response()->json(['message' => 'Amount updated successfully.']);
        }

        return response()->json(['message' => 'Record not found.'], 404);
    }

    public function manageRecoveryPost(Request $request)
    {
        $client = $this->clientService->firstOrCreateClient([
            'phone' => $request->phone,
            'name' => $request->name,
            'company' => $request->company,
            'email' => $request->email
        ]);

        $project = $this->clientService->updateOrCreateProject(array_merge($request->all(), [
            'client_id' => $client->id,
            'project_name' => $request->project
        ]), $request->id);

        $checkProject = Recoveries::where('project_id', '=', $project->id)->count();

        if ($checkProject == 0) {
            $this->clientService->recordRecovery([
                'client_id' => $client->id,
                'project_id' => $project->id,
                'received' => $request->received,
                'note' => $request->note,
                'reminderDate' => $request->reminder ?: now(),
                'status' => $request->status ?: '0'
            ]);
            return redirect('recoveries')->with('success', 'Operation completed successfully.');
        }

        return back()->with('success', 'Recovery details updated successfully.');
    }

    public function contracts()
    {
        if (Auth::user()->role == 'master') {
            $contracts = Contracts::leftjoin('clients', 'contracts.client_id', '=', 'clients.id')
                ->select('clients.name', 'clients.email', 'clients.company', 'contracts.*')
                ->orderBy('contracts.end_date', 'DESC')
                ->get();
        } else {
            $contracts = Contracts::leftjoin('clients', 'contracts.client_id', '=', 'clients.id')
                ->select('clients.name', 'clients.email', 'clients.company', 'contracts.*')
                ->where('clients.cid', '=', Auth::user()->cid)
                ->orderByRaw("
                    CASE contracts.status
                        WHEN 'Draft' THEN 1
                        WHEN 'Sent' THEN 2
                        WHEN 'Accepted' THEN 3
                        WHEN 'Declined' THEN 4
                        WHEN 'Expired' THEN 5
                        ELSE 6
                    END
                ")
                ->orderBy('contracts.end_date', 'DESC')
                ->get();
        }

        // Add priority and rowClass
        $contracts = $contracts->map(function ($contract) {
            $endDate = \Carbon\Carbon::parse($contract->end_date ?? null);
            $today = \Carbon\Carbon::today();
            $diffInDays = $today->diffInDays($endDate, false);

            if ($diffInDays < 0) {
                $priority = 1; // expired
                $rowClass = 'table-danger';
            } elseif ($diffInDays <= 7) {
                $priority = 2; // critical
                $rowClass = 'table-warning';
            } elseif ($diffInDays <= 15) {
                $priority = 3; // warning
                $rowClass = 'table-warning';
            } elseif ($diffInDays <= 30) {
                $priority = 4; // mild warning
                $rowClass = 'table-warning';
            } else {
                $priority = 5; // normal
                $rowClass = '';
            }

            $contract->priority = $priority;
            $contract->rowClass = $rowClass;
            return $contract;
        })
            ->sortBy([
                ['priority', 'asc'],
                ['end_date', 'asc']
            ])
            ->values();

        return view('contracts', ['contracts' => $contracts]);
    }

    public function manageContract(Request $request)
    {
        $id = $request->id;
        $contract = null;

        if ($id) {
            $contract = Contracts::where('id', '=', $id)
                ->first();
        }

        $clients = Clients::where('status', '=', '1')->get();

        return view('manageContract', [
            'contract' => $contract,
            'clients' => $clients,
        ]);
    }

    public function manageContractPost(Request $request)
    {
        $validatedData = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'subject' => 'required|string|max:255',
            'value' => 'nullable|numeric',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'contract_type' => 'required|string|max:255',
            'custom_contract_type' => 'nullable|string|max:255',
        ]);

        // Use custom contract type if provided
        $contractType = $validatedData['contract_type'] === 'new'
            ? $validatedData['custom_contract_type']
            : $validatedData['contract_type'];

        if ($contractType === null) {
            return back()->withErrors(['custom_contract_type' => 'Please enter a custom contract type.'])->withInput();
        }

        // Check if this is an update or new
        $contract = $request->id ? Contracts::findOrFail($request->id) : new Contracts();

        $contract->client_id = $validatedData['client_id'];
        $contract->subject = $validatedData['subject'];
        $contract->value = $validatedData['value'];
        $contract->start_date = $validatedData['start_date'];
        $contract->end_date = $validatedData['end_date'];
        $contract->des = $validatedData['description'] ?? '';
        $contract->contract_type = $contractType;

        $contract->save();

        return redirect('/contracts')->with('success', $request->id ? 'Contract updated successfully.' : 'Contract added successfully.');
    }

    public function projects(Request $request)
    {
        $search = $request->get('search');
        $query = Projects::leftJoin('clients', 'projects.client_id', '=', 'clients.id')
            ->leftJoin(
                DB::raw('(SELECT project_id, SUM(paid) as total_paid FROM recoveries GROUP BY project_id) as rec_totals'),
                'projects.id', '=', 'rec_totals.project_id'
            )
            ->select(
                'projects.*',
                'clients.name as client_name',
                'clients.company as client_company',
                DB::raw('COALESCE(rec_totals.total_paid, 0) as total_paid')
            );

        if (Auth::user()->role != 'master') {
            $query->where('projects.cid', '=', Auth::user()->cid);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('projects.name', 'LIKE', "%{$search}%")
                  ->orWhere('clients.name', 'LIKE', "%{$search}%")
                  ->orWhere('clients.company', 'LIKE', "%{$search}%");
            });
        }

        $projects = $query->orderBy('projects.id', 'DESC')->get();

        return view('projects', ['projects' => $projects, 'search' => $search]);
    }

    public function singleProjectGet(Request $request)
    {
        $id = $request->id;
        $project = Projects::leftJoin('clients', 'projects.client_id', '=', 'clients.id')
            ->select('projects.*', 'clients.name as client_name', 'clients.company as client_company', 'clients.email as client_email', 'clients.mob as client_mob', 'clients.location as client_location')
            ->where('projects.id', $id)
            ->first();

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $recoveries = Recoveries::where('project_id', $id)->orderBy('id', 'DESC')->get();
        $license = Eselicenses::where('project_id', $id)->orderBy('id', 'DESC')->first();

        return response()->json([
            'project' => $project,
            'recoveries' => $recoveries,
            'license' => $license
        ]);
    }

    public function viewProject(Request $request, $id)
    {
        $project = Projects::leftJoin('clients', 'projects.client_id', '=', 'clients.id')
            ->select('projects.*', 'clients.name as client_name', 'clients.company as client_company', 'clients.email as client_email', 'clients.mob as client_mob', 'clients.location as client_location', 'clients.whatsapp as client_whatsapp')
            ->where('projects.id', $id)
            ->first();

        if (!$project) {
            abort(404, 'Project not found');
        }

        $recoveries = Recoveries::where('project_id', $id)->orderBy('id', 'DESC')->get();
        $license = Eselicenses::where('project_id', $id)->orderBy('id', 'DESC')->first();
        $invoices = Invoices::where('client_id', $project->client_id)->orderBy('id', 'DESC')->get();

        // Tasks related to project
        $tasks = \App\Models\CrmTask::where('rel_type', 'Project')->where('rel_id', $id)->orderBy('due_date', 'asc')->get();

        // Proposals related to client/lead
        $client = \App\Models\Clients::find($project->client_id);
        $leadIds = [$project->client_id];
        if ($client && !empty($client->commentLeadID)) {
            $leadIds[] = $client->commentLeadID;
        }
        $proposals = \App\Models\Proposals::whereIn('lead_id', $leadIds)->orderBy('id', 'DESC')->get();

        return view('project-view', compact('project', 'recoveries', 'license', 'invoices', 'tasks', 'proposals'));
    }

    public function licensing()
    {

        if (Auth::user()->role == 'master') {

            $licenses = Eselicenses::leftjoin('projects', 'eselicenses.project_id', 'projects.id')
                ->leftjoin('clients', 'projects.client_id', 'clients.id')
                ->select('clients.name as client_name', 'projects.*', 'eselicenses.*')
                ->orderBy('eselicenses.expiry_date', 'ASC')->get();

        } else {

            $licenses = Eselicenses::leftjoin('projects', 'eselicenses.project_id', 'projects.id')
                ->leftjoin('clients', 'projects.client_id', 'clients.id')
                ->select('clients.name as client_name', 'projects.*', 'eselicenses.*')
                ->where('projects.cid', '=', Auth::user()->cid)
                ->orderBy('eselicenses.expiry_date', 'ASC')->get();

        }

        return view('licenses', ['licenses' => $licenses]);

    }

    public function manageLicense(Request $request)
    {
        $id = $request->id ?? '';
        $license = Eselicenses::leftjoin('projects', 'eselicenses.project_id', 'projects.id')
            ->leftjoin('clients', 'projects.client_id', 'clients.id')
            ->select('clients.name as client_name', 'clients.company', 'clients.mob', 'clients.email', 'projects.*', 'eselicenses.*')
            ->where('eselicenses.id', '=', $id)->first();

        if (Auth::user()->role == 'master') {

            $projects = Projects::leftjoin('clients', 'clients.id', 'projects.client_id')
                ->select('clients.name as client_name', 'clients.company', 'clients.email', 'clients.mob', 'clients.location', 'projects.*')
                ->orderBy('name', 'ASC')->get();

        } else {

            $projects = Projects::leftjoin('clients', 'clients.id', 'projects.client_id')
                ->select('clients.name as client_name', 'clients.company', 'clients.email', 'clients.mob', 'clients.location', 'projects.*')
                ->where('projects.cid', '=', Auth::user()->cid)
                ->orderBy('name', 'ASC')->get();

        }

        return view('manageLicense', ['license' => $license, 'projects' => $projects]);

    }

    public function manageLicensePost(Request $request)
    {
        $validatedData = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'project_name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'cost' => 'nullable|numeric',
            'website' => 'required|url|max:255',
            'technology_stack' => 'required|string|max:255',
            'note' => 'nullable|string',
            'license_key' => 'required|string|max:255|unique:eselicenses,eselicense_key,' . ($request->id ?? 'NULL'),
            'expiry_date' => 'required|date',
        ]);

        $client = $this->clientService->firstOrCreateClient($validatedData);
        $project = $this->clientService->updateOrCreateProject(array_merge($validatedData, ['client_id' => $client->id]), $request->project_id);

        $license = $request->id ? Eselicenses::findOrFail($request->id) : new Eselicenses();
        $license->fill([
            'project_id' => $project->id,
            'eselicense_key' => $request->license_key,
            'expiry_date' => $request->expiry_date,
            'technology_stack' => $request->technology_stack
        ]);

        if ($license->save()) {
            return redirect('licensing')->with('success', 'License details successfully processed.');
        }

        return back()->with('error', 'Failed to process license.');
    }

    public function clients(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $industry = $request->input('industry');
        $lifecycle_stage = $request->input('lifecycle_stage');

        $query = Clients::where('name', '!=', '');

        if (Auth::user()->role != 'master') {
            $query->where('cid', '=', Auth::user()->cid);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('company', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('mob', 'LIKE', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('status', '=', $status);
        }

        if (!empty($industry)) {
            $query->where('industry', '=', $industry);
        }

        if (!empty($lifecycle_stage)) {
            $query->where('lifecycle_stage', '=', $lifecycle_stage);
        }

        $clients = $query->orderBy('status', 'DESC')->orderBy('id', 'DESC')->get();
        
        // Dynamically fetch available industries for the dropdown
        $industryQuery = Clients::select('industry')->whereNotNull('industry')->where('industry', '!=', '')->distinct();
        if (Auth::user()->role != 'master') {
            $industryQuery->where('cid', '=', Auth::user()->cid);
        }
        $availableIndustries = $industryQuery->pluck('industry');

        return view('clients', [
            'clients' => $clients,
            'search' => $search,
            'status' => $status,
            'industry' => $industry,
            'lifecycle_stage' => $lifecycle_stage,
            'availableIndustries' => $availableIndustries
        ]);
    }

    public function clientPost(Request $request)
    {
        return $this->clients($request);
    }

    public function manageClient(Request $request)
    {
        $clients = Clients::with('departments')->where('id', '=', $request->id)->first();
        $leadOrigin = null;
        $interactions = collect();
        $proposals = collect();
        $projects = collect();
        $invoices = collect();

        if ($request->id && $clients) {
            $interactions = \App\Models\Interaction::where('rel_type', 'Client')
                ->where('rel_id', $request->id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Fetch Journey Data
            if (!empty($clients->commentLeadID)) {
                $leadOrigin = \App\Models\Leads::find($clients->commentLeadID);
            }

            // Proposals (related to this client, or the original lead)
            $leadIds = [$clients->id];
            if ($leadOrigin)
                $leadIds[] = $leadOrigin->id;

            $proposals = \App\Models\Proposals::whereIn('lead_id', $leadIds)
                ->orderBy('created_at', 'desc')
                ->get();

            $projects = \App\Models\Projects::where('client_id', $request->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $invoices = \App\Models\Invoices::where('client_id', $request->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('manageClient', [
            'clients' => $clients,
            'interactions' => $interactions,
            'leadOrigin' => $leadOrigin,
            'proposals' => $proposals,
            'projects' => $projects,
            'invoices' => $invoices
        ]);

    }

    public function storeInteraction(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'type' => 'required|string',
            'content' => 'required_without:attachment',
            'attachment' => 'nullable|file|max:10240' // max 10MB
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('interactions', 'public');
        }

        \App\Models\Interaction::create([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'rel_type' => 'Client',
            'rel_id' => $request->client_id,
            'type' => $request->type,
            'content' => $request->input('content'),
            'attachment_path' => $path
        ]);

        return back()->with('success', 'Interaction/Document added successfully.');
    }

    public function manageClientPost(Request $request)
    {
        $location = json_encode($request->address ?? []);

        if (empty($request->id)) {
            // Convert lead to client
            $client = new Clients();
            $client->cid = Auth::user()->cid ?? '';
            $client->name = $request->name ?? '';
            $client->company = $request->company ?? '';
            $client->gstno = $request->gst ?? '';
            $client->email = $request->email ?? '';
            $client->mob = $request->mob ?? '';
            $client->alterMob = $request->alterMob ?? '';
            $client->location = $location ?? '';
            $client->source = $request->source ?? '';
            $client->poc = $request->poc ?? '';
            $client->purpose = $request->purpose ?? '';
            $client->status = '1';
            $client->whatsapp = $request->whatsapp ?? '';
            $client->industry = $request->industry ?? '';
            $client->position = $request->position ?? '';
            $client->website = $request->website ?? '';
            $client->values = $request->values ?? '';
            $client->language = $request->language ?? '';
            $client->tags = $request->tags ?? '';
            $client->lifecycle_stage = $request->lifecycle_stage ?? null;

            if ($client->save()) {
                // Save Departments
                $submittedDeptIds = [];
                if ($request->has('departments')) {
                    foreach ($request->departments as $dept) {
                        if (!empty($dept['name'])) {
                            $d = CustomerDepartments::updateOrCreate(
                                ['id' => $dept['id'] ?? null],
                                [
                                    'client_id' => $client->id,
                                    'name' => $dept['name'],
                                    'location' => $dept['location'] ?? null,
                                    'poc' => $dept['poc'] ?? null,
                                ]
                            );
                            $submittedDeptIds[] = $d->id;
                        }
                    }
                }
                // Delete removed departments
                CustomerDepartments::where('client_id', $client->id)
                    ->whereNotIn('id', $submittedDeptIds)
                    ->delete();

                return redirect('clients')->with('success', 'New customer successfully added.');
            } else {
                return back()->with('error', 'Failed to list new client.');
            }
        } else {
            // Updating an existing lead or converting to a client
            $id = $request->id ?? '';
            $leadSingle = Clients::find($id);

            if (!$leadSingle) {
                return back()->with('error', 'Client not found.');
            }

            // Update existing lead
            $leadSingle->cid = Auth::user()->cid ?? '';
            $leadSingle->name = $request->name ?? '';
            $leadSingle->company = $request->company ?? '';
            $leadSingle->gstno = $request->gst ?? '';
            $leadSingle->email = $request->email ?? '';
            $leadSingle->mob = $request->mob ?? '';
            $leadSingle->alterMob = $request->alterMob ?? '';
            $leadSingle->location = $location ?? '';
            $leadSingle->source = $request->source ?? '';
            $leadSingle->poc = $request->poc ?? '';
            $leadSingle->purpose = $request->purpose ?? '';
            $leadSingle->status = $request->status ?? '10';
            $leadSingle->whatsapp = $request->whatsapp ?? '';
            $leadSingle->industry = $request->industry ?? '';
            $leadSingle->position = $request->position ?? '';
            $leadSingle->website = $request->website ?? '';
            $leadSingle->values = $request->values ?? '';
            $leadSingle->language = $request->language ?? '';
            $leadSingle->tags = $request->tags ?? '';
            $leadSingle->lifecycle_stage = $request->lifecycle_stage ?? null;

            if ($leadSingle->update()) {
                // Save Departments
                $submittedDeptIds = [];
                if ($request->has('departments')) {
                    foreach ($request->departments as $dept) {
                        if (!empty($dept['name'])) {
                            $d = CustomerDepartments::updateOrCreate(
                                ['id' => $dept['id'] ?? null],
                                [
                                    'client_id' => $leadSingle->id,
                                    'name' => $dept['name'],
                                    'location' => $dept['location'] ?? null,
                                    'poc' => $dept['poc'] ?? null,
                                ]
                            );
                            $submittedDeptIds[] = $d->id;
                        }
                    }
                }
                // Delete removed departments
                CustomerDepartments::where('client_id', $leadSingle->id)
                    ->whereNotIn('id', $submittedDeptIds)
                    ->delete();

                return back()->with('success', 'client successfully updated.');
            } else {
                return back()->with('error', 'Failed to update lead.');
            }

        }
    }

    public function singleClientGet(Request $request)
    {
        $id = ($request->id ?? '');
        $page = ($request->pagename ?? '');
        if ($page == 'client') {

            $client = Clients::with('departments')->where('id', '=', $id)->first();
            if (!$client) return response()->json(['error' => 'Client not found'], 404);

            $interactions = \App\Models\Interaction::where('rel_type', 'Client')
                ->where('rel_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            $leadOrigin = null;
            if (!empty($client->commentLeadID)) {
                $leadOrigin = \App\Models\Leads::find($client->commentLeadID);
            }

            $leadIds = [$client->id];
            if ($leadOrigin) $leadIds[] = $leadOrigin->id;

            $proposals = \App\Models\Proposals::whereIn('lead_id', $leadIds)
                ->orderBy('created_at', 'desc')
                ->get();

            $projects = \App\Models\Projects::where('client_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            $invoices = \App\Models\Invoices::where('client_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'clients' => $client,
                'interactions' => $interactions,
                'proposals' => $proposals,
                'projects' => $projects,
                'invoices' => $invoices
            ]);
        }
    }

    public function invoices()
    {

        $invoices = Invoices::leftJoin('clients', 'invoices.client_id', '=', 'clients.id')
            ->select('clients.name as client_name', 'clients.company as client_company', 'invoices.*')
            ->where('clients.cid', '=', Auth::User()->cid)
            ->orderBy('id', 'DESC')->get();

        return view('invoices', ['invoices' => $invoices]);

    }

    public function manageInvoice(Request $request)
    {
        $id = $request->id ?? null; // or just $request->id

        // If there's an ID, load one invoice
        if ($id) {
            // `first()` returns a single model or null (not a collection).
            $invoice = Invoices::where('id', $id)->first();
            // Alternatively: $invoice = Invoices::find($id);

            // Get items for that single invoice
            $invoiceItems = Invoice_items::where('invoice_id', $id)->get();
        } else {
            // No ID means we're creating a NEW invoice
            // You can create a blank model or set $invoice = null
            $invoice = null;
            // No items for a new invoice
            $invoiceItems = collect();
        }

        $clients = Clients::leftJoin('projects', 'clients.id', '=', 'projects.client_id')
            ->select('projects.name as project_name', 'clients.*')
            ->where('clients.cid', '=', Auth::User()->cid)->get();

        $companies = Companies::where('id', '=', Auth::User()->cid)->first();

        return view('manageInvoice', [
            'invoice' => $invoice,
            'invoiceItems' => $invoiceItems,
            'clients' => $clients,
            'companies' => $companies,
        ]);
    }

    public function manageInvoicePost(Request $request)
    {
        $validatedData = $request->validate([
            'invoice_number' => 'required|max:255',
            'invoice_type' => 'nullable|max:255',
            'client_id' => 'required|integer|exists:clients,id',
            'date' => 'required|date',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:unpaid,paid,partial',
            'reference' => 'nullable|string|max:255',

            'payment_mode' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:10',
            'sales_agent' => 'nullable|string|max:255',
            'discount_type' => 'nullable|in:none,before-tax,after-tax',
            'recurring_invoice' => 'nullable|boolean',

            'billing_address' => 'nullable|string',
            'client_gst' => 'nullable|string',
            'shipping_address' => 'nullable|string',

            'discount_mode' => 'nullable|in:flat,percentage',
            'discount_value' => 'nullable|numeric',
            'adjustment' => 'nullable|numeric',

            'admin_note' => 'nullable|string',
            'client_note' => 'nullable|string',
            'terms' => 'nullable|string',

            // If you're editing an existing invoice
            'id' => 'nullable|integer|exists:invoices,id',
        ]);

        // 2) Check if we are updating or creating a new invoice
        if (!empty($validatedData['id'])) {
            // Update existing invoice
            $invoice = Invoices::findOrFail($validatedData['id']);
        } else {
            // Create new invoice
            $invoice = new Invoices();
        }

        // 3) Assign validated data to the invoice model
        $invoice->invoice_number = $validatedData['invoice_number'];
        $invoice->invoice = $validatedData['invoice_type'];
        $invoice->client_id = $validatedData['client_id'];
        $invoice->date = $validatedData['date'];
        $invoice->due_date = $validatedData['due_date'] ?? null;
        $invoice->status = $validatedData['status'] ?? 'unpaid';
        $invoice->reference = $validatedData['reference'] ?? null;

        $invoice->payment_mode = $validatedData['payment_mode'] ?? null;
        $invoice->currency = $validatedData['currency'] ?? 'USD';
        $invoice->sales_agent = $validatedData['sales_agent'] ?? null;
        $invoice->discount_type = $validatedData['discount_type'] ?? 'none';
        $invoice->recurring_invoice = !empty($validatedData['recurring_invoice']);

        $invoice->bank_details = json_encode($request->bank_details ?? []);
        $invoice->billing_address = $validatedData['billing_address'] ?? null;
        $invoice->client_gstno = $validatedData['client_gst'] ?? null;
        $invoice->shipping_address = $validatedData['shipping_address'] ?? null;

        $invoice->discount_mode = $validatedData['discount_mode'] ?? 'flat';
        $invoice->discount = $validatedData['discount_value'] ?? 0;
        $invoice->adjustment = $validatedData['adjustment'] ?? 0;
        $invoice->total_amount = $request->gtAmount ?? 0;

        $invoice->admin_note = $validatedData['admin_note'] ?? null;
        $invoice->client_note = $validatedData['client_note'] ?? null;
        $invoice->terms = $validatedData['terms'] ?? null;

        // 4) Save the invoice to get an ID (if new)
        $invoice->save();

        if ($request->has('invoice_items')) {
            // Remove old items if updating (optional)
            if (!empty($validatedData['id'])) {
                Invoice_items::where('invoice_id', $invoice->id)->delete();
            }

            foreach ($request->input('invoice_items', []) as $itemData) {
                // --- Extract Basic Item Data ---
                $shortDesc = $itemData['short_description'] ?? '';
                $longDesc = $itemData['long_description'] ?? '';
                $sac_code = $itemData['sac_code'] ?? '';
                // Use float for quantity if you allow fractional quantities (like hours)
                $quantity = !empty($itemData['quantity']) ? (float) $itemData['quantity'] : 0;
                $price = !empty($itemData['price']) ? (float) $itemData['price'] : 0;

                // --- Skip Empty/Meaningless Rows ---
                // Skip if description/name is missing AND quantity or price is zero/missing
                if (empty($shortDesc) && empty($longDesc) && ($quantity <= 0 || $price <= 0)) {
                    continue;
                }

                // --- START: Parse Tax Rates ---
                $selected_tax_values = isset($itemData['tax_rate']) && is_array($itemData['tax_rate'])
                    ? $itemData['tax_rate']
                    : [];

                $cgst_percent = 0.0;
                $sgst_percent = 0.0;
                $igst_percent = 0.0;
                $vat_percent = 0.0;
                // Add other tax types if necessary

                foreach ($selected_tax_values as $tax_value_string) {
                    // $tax_value_string will be like "0:0.0500", "1:0.0500", etc.
                    $parts = explode(':', $tax_value_string);

                    if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                        $tax_index = (int) $parts[0];
                        $tax_rate_decimal = (float) $parts[1];
                        $tax_rate_percent = $tax_rate_decimal * 100.0; // Convert to percentage

                        switch ($tax_index) {
                            case 0:
                                $cgst_percent = $tax_rate_percent;
                                break;
                            case 1:
                                $sgst_percent = $tax_rate_percent;
                                break;
                            case 2:
                                $igst_percent = $tax_rate_percent;
                                break;
                            case 3:
                                $vat_percent = $tax_rate_percent;
                                break;
                            // Add more cases if needed
                            default:
                                // Log::warning("Unexpected tax index [{$tax_index}] found for invoice ID [{$invoice->id}]");
                                break;
                        }
                    } else {
                        // Log::warning("Malformed tax value '{$tax_value_string}' received for invoice ID [{$invoice->id}]");
                    }
                }
                // --- END: Parse Tax Rates ---


                // --- Create & Save Invoice Item ---
                $invoiceItem = new Invoice_items();
                $invoiceItem->invoice_id = $invoice->id;
                $invoiceItem->short_description = $shortDesc;
                $invoiceItem->long_description = $longDesc;
                $invoiceItem->sac_code = $sac_code;
                $invoiceItem->quantity = $quantity; // Ensure your DB column can handle float if needed
                $invoiceItem->price = $price;

                // Assign the *parsed* tax percentages
                $invoiceItem->cgst_percent = $cgst_percent;
                $invoiceItem->sgst_percent = $sgst_percent;
                $invoiceItem->igst_percent = $igst_percent;
                $invoiceItem->vat_percent = $vat_percent;
                // Add assignments for other tax types if you have them

                $invoiceItem->save();
            }
        }

        // 6) Redirect or return a response
        return redirect()
            ->route('manageInvoice', ('id=' . $invoice->id ?? ''))
            ->with('success', 'Invoice saved successfully!');
    }

    public function manageInvoiceClientPost(Request $request)
    {
        $client = new Clients();
        $client->cid = Auth::user()->cid ?? '';
        $client->name = $request->name ?? '';
        $client->company = $request->company ?? '';
        $client->email = $request->email ?? '';
        $client->mob = $request->mob ?? '';
        $client->alterMob = $request->alterMob ?? '';
        $client->location = json_encode($request->address ?? '');
        $client->source = $request->source ?? '';
        $client->poc = $request->poc ?? '';
        $client->purpose = $request->purpose ?? '';
        $client->status = '0';
        $client->whatsapp = $request->whatsapp ?? '';
        $client->industry = $request->industry ?? '';
        $client->position = $request->position ?? '';
        $client->website = $request->website ?? '';
        $client->values = $request->values ?? '';
        $client->language = $request->language ?? '';
        $client->tags = $request->tags ?? '';

        if ($client->save()) {
            return back()->with('success', 'New Client successfully added.');
        } else {
            return back()->with('error', 'Failed to convert lead to client.');
        }

    }

    public function invoicePreview($id)
    {
        // Fetch the invoice with client details
        $invoice = Invoices::leftJoin('clients', 'invoices.client_id', '=', 'clients.id')
            ->leftJoin('companies', 'clients.cid', '=', 'companies.id')
            ->select('companies.name as cn', 'companies.mob as cm', 'companies.email as ce', 'companies.img', 'companies.gst as cgst', 'companies.vat as cvat', 'companies.address', 'companies.city', 'companies.state', 'companies.zipcode', 'companies.country', 'companies.bank_details', 'clients.name', 'clients.company', 'clients.email', 'clients.mob', 'clients.location', 'invoices.*')
            ->where('invoices.id', '=', $id)
            ->first();

        // Fetch the invoice items
        $invoice_items = Invoice_items::where('invoice_id', '=', $id)->get(); // Corrected query

        // Check if invoice exists before proceeding
        if (!$invoice) {
            return abort(404, 'Invoice not found');
        }

        // Pass both invoice and invoice items to the view
        return view('invoices.preview', compact('invoice', 'invoice_items'));
    }

    public function invoicePdfPreview($id)
    {
        // Fetch the invoice with client details
        $invoice = Invoices::leftJoin('clients', 'invoices.client_id', '=', 'clients.id')
            ->leftJoin('companies', 'clients.cid', '=', 'companies.id')
            ->select('companies.name as cn', 'companies.mob as cm', 'companies.email as ce', 'companies.img', 'companies.gst as cgst', 'companies.vat as cvat', 'companies.address', 'companies.city', 'companies.state', 'companies.zipcode', 'companies.country', 'companies.bank_details', 'clients.name', 'clients.company', 'clients.email', 'clients.mob', 'clients.location', 'invoices.*')
            ->where('invoices.id', '=', $id)
            ->first();

        // Fetch the invoice items
        $invoice_items = Invoice_items::where('invoice_id', '=', $id)->get();

        // Get company logo in base64
        $imagePath = public_path('assets/images/company/' . $invoice->img); // Local path
        $type = pathinfo($imagePath, PATHINFO_EXTENSION);
        $data = file_get_contents($imagePath);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

        // Load the PDF view for preview
        $pdf = Pdf::loadView('invoices.download', compact('invoice', 'invoice_items', 'base64'));

        // Remove all characters except letters and digits
        $invoice->invoice_number = preg_replace('/[^A-Za-z0-9]/', '', $invoice->invoice_number);

        // Preview the PDF in browser
        return $pdf->stream('Invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function invoiceDownload($id)
    {
        // Fetch the invoice with client details
        $invoice = Invoices::leftJoin('clients', 'invoices.client_id', '=', 'clients.id')
            ->leftJoin('companies', 'clients.cid', '=', 'companies.id')
            ->select('companies.name as cn', 'companies.mob as cm', 'companies.email as ce', 'companies.img', 'companies.gst as cgst', 'companies.vat as cvat', 'companies.address', 'companies.city', 'companies.state', 'companies.zipcode', 'companies.country', 'companies.bank_details', 'clients.name', 'clients.company', 'clients.email', 'clients.mob', 'clients.location', 'invoices.*')
            ->where('invoices.id', '=', $id)
            ->first();

        // Fetch the invoice items
        $invoice_items = Invoice_items::where('invoice_id', '=', $id)->get();

        $imagePath = public_path('assets/images/company/' . $invoice->img); // Local path
        $type = pathinfo($imagePath, PATHINFO_EXTENSION);
        $data = file_get_contents($imagePath);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

        // Load the PDF view
        $pdf = Pdf::loadView('invoices.download', compact('invoice', 'invoice_items', 'base64'));

        // Remove all characters except letters and digits
        $invoice->invoice_number = preg_replace('/[^A-Za-z0-9]/', '', $invoice->invoice_number);

        return $pdf->download('Invoice-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Show the form for creating / editing a project.
     */
    public function manageProject(Request $request)
    {
        $id = $request->id ?? null;
        $project = null;

        if ($id) {
            $project = Projects::leftJoin('clients', 'projects.client_id', '=', 'clients.id')
                ->select('projects.*', 'clients.name as client_name', 'clients.company as client_company')
                ->where('projects.id', $id)
                ->first();
        }

        // Load clients for the dropdown (only current company)
        $clients = Clients::where('cid', '=', Auth::user()->cid)
            ->where('name', '!=', '')
            ->orderBy('name', 'ASC')
            ->get(['id', 'name', 'company']);

        return view('manageProject', [
            'project' => $project,
            'clients' => $clients,
        ]);
    }

    /**
     * Save (create or update) a project.
     */
    public function manageProjectPost(Request $request)
    {
        $request->validate([
            'client_id'  => 'required|exists:clients,id',
            'name'       => 'required|string|max:255',
            'type'       => 'nullable|string|max:100',
            'amount'     => 'nullable|numeric|min:0',
            'note'       => 'nullable|string',
            'deployment_url' => 'nullable|url|max:255',
        ]);

        $project = $request->id ? Projects::findOrFail($request->id) : new Projects();

        $project->cid            = Auth::user()->cid;
        $project->client_id      = $request->client_id;
        $project->name           = $request->name;
        $project->type           = $request->type ?? '';
        $project->amount         = $request->amount ?? 0;
        $project->note           = $request->note ?? '';
        $project->deployment_url = $request->deployment_url ?? '';
        $project->save();

        return redirect('/projects')->with('success', $request->id
            ? 'Project updated successfully.'
            : 'Project created successfully.'
        );
    }
}
