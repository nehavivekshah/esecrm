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

class ClientController extends Controller
{
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
        $client = Clients::where('id', ($request->client_id ?? ''))->first();
        if (($request->received ?? '') > 0) {
            $recoveries = new Recoveries();

            $recoveries->cid = (Auth::user()->cid ?? '');
            $recoveries->client_id = ($request->client_id ?? '');
            $recoveries->project_id = ($request->project_id ?? '');
            $recoveries->paid = ($request->received ?? '');
            $recoveries->note = ($request->note ?? '');
            $recoveries->status = ($request->status ?? '0');
        } else {
            $recoveries = new Recoveries();

            $recoveries->cid = (Auth::user()->cid ?? '');
            $recoveries->client_id = ($request->client_id ?? '');
            $recoveries->project_id = ($request->project_id ?? '');
            $recoveries->paid = ($request->received ?? '');
            $recoveries->note = ($request->note ?? '');
            $recoveries->reminder = ($request->reminderDate ?? '');
            $recoveries->status = ($request->status ?? '0');
        }

        if ($recoveries->save()) {

            if (($request->send ?? '') == '1' && ($request->received ?? '') > 0) {

                $to = $client->email ?? '';
                $subject = 'Thank You !!';

                $message = "<p style='font-weight:bold;'>Payment Received</p>" . ($request->note ?? '');

                $viewName = 'emails.welcome';
                $viewData = ["name" => ($client->name ?? 'User'), "messages" => $message];

                if (!empty($client->email)) {

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
                }
                return redirect('recoveries')->with('success', 'This recovery details was successfully added to the Recovery Table.');

            } else {

                $to = $client->email ?? '';
                $subject = 'Payment Reminder Alert!!';

                $message = "<b>Reminder Date:</b> " . (date_format(date_create(($request->reminderDate ?? '')), 'd M, Y')) . "<br><b>Remaining Bal.</b>" . ($request->bal ?? '') . "<br>" . ($request->note ?? '');

                $viewName = 'emails.welcome';
                $viewData = ["name" => ($client->name ?? 'User'), "messages" => $message];

                if (!empty($client->email)) {

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
                }

                return redirect('recoveries')->with('success', 'Payment reminder successfully updated.');

            }

        } else {
            return back()->with('error', 'Failed to add this Recovery to the Recovery table.');
        }
    }

    public function recoveries()
    {
        $recoveries = Recoveries::leftJoin('clients', 'recoveries.client_id', '=', 'clients.id')
            ->leftJoin('projects', 'recoveries.project_id', '=', 'projects.id')
            ->select(
                'projects.id',
                'clients.batchNo',
                'clients.name',
                'clients.company',
                'clients.mob',
                'clients.whatsapp',
                'clients.industry',
                'clients.email',
                'clients.poc',
                DB::raw('MAX(projects.name) as project'),
                DB::raw('MAX(projects.amount) as project_amount'),
                DB::raw('MAX(projects.note) as project_note'),
                DB::raw('MAX(projects.deployment_url) as deployment_url'),
                DB::raw('MAX(projects.amount) - SUM(recoveries.paid) as remaining_amount'),
                DB::raw('MAX(recoveries.reminder) as reminder'),
                'recoveries.status'
            )
            ->where('recoveries.cid', '=', Auth::user()->cid)
            ->groupBy(
                'projects.id',
                'clients.batchNo',
                'clients.name',
                'clients.company',
                'clients.mob',
                'clients.whatsapp',
                'clients.industry',
                'clients.email',
                'clients.poc',
                'recoveries.status'
            )
            ->orderByRaw("
                CASE
                    WHEN MAX(projects.amount) - SUM(recoveries.paid) = 0 THEN 2  -- Rows with remaining_amount 0 go last
                    WHEN recoveries.status = 0 
                         AND DATE(MAX(recoveries.reminder)) <= CURDATE() 
                         AND TIME(MAX(recoveries.reminder)) <= CURTIME() THEN 0 -- Overdue first
                    ELSE 1 -- Rest of the rows second
                END,
                MAX(recoveries.reminder) DESC
            ")
            ->get();

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

        $customer = Clients::where('mob', '=', ($request->phone ?? ''))->first();

        if (empty($customer->id)) {
            $client = new Clients();

            $client->cid = (Auth::user()->cid ?? '');
            $client->commentLeadID = ($request->commentLeadID ?? '0');
            $client->batchNo = ($request->btno ?? '');
            $client->name = ($request->name ?? '');
            $client->company = ($request->company ?? '');
            $client->email = ($request->email ?? '');
            $client->mob = ($request->phone ?? '');
            $client->location = ($request->location ?? '');
            $client->purpose = ($request->purpose ?? '');
            $client->source = ($request->source ?? '');
            $client->poc = ($request->executive ?? '');
            $client->whatsapp = ($request->whatsapp ?? '');
            $client->industry = ($request->industry ?? '');
            $client->website = ($request->website ?? '');
            $client->position = ($request->position ?? '');
            $client->values = ($request->values ?? '');
            $client->language = ($request->language ?? '');
            $client->status = ($request->status ?? '0');
            $client->save();
        } else {
            $customer->cid = (Auth::user()->cid ?? '');
            $customer->commentLeadID = ($request->commentLeadID ?? '0');
            $customer->batchNo = ($request->btno ?? '');
            $customer->name = ($request->name ?? '');
            $customer->company = ($request->company ?? '');
            $customer->email = ($request->email ?? '');
            $customer->mob = ($request->phone ?? '');
            $customer->location = ($request->location ?? '');
            $customer->purpose = ($request->purpose ?? '');
            $customer->source = ($request->source ?? '');
            $customer->poc = ($request->executive ?? '');
            $customer->whatsapp = ($request->whatsapp ?? '');
            $customer->industry = ($request->industry ?? '');
            $customer->website = ($request->website ?? '');
            $customer->position = ($request->position ?? '');
            $customer->values = ($request->values ?? '');
            $customer->language = ($request->language ?? '');
            $customer->status = ($request->status ?? '0');
            $customer->save();
        }

        $client_id = empty($customer->id) ? $client->id : $customer->id;
        $cp = Projects::where('id', '=', ($request->id ?? ''))->first();

        if (empty($cp->id)) {

            $project = new Projects();

            $project->cid = (Auth::user()->cid ?? '');
            $project->client_id = ($client_id ?? '');
            $project->name = ($request->project ?? '');
            $project->type = ($request->type ?? '');
            $project->amount = ($request->amount ?? '');
            $project->note = ($request->note ?? '');
            $project->deployment_url = ($request->website ?? '');
            $project->status = ($request->status ?? '0');
            $project->save();

        } else {

            $cp->cid = (Auth::user()->cid ?? '');
            $cp->client_id = ($client_id ?? '');
            $cp->name = ($request->project ?? '');
            $cp->type = ($request->type ?? '');
            $cp->amount = ($request->amount ?? '');
            $cp->note = ($request->note ?? '');
            $cp->website = ($request->website ?? '');
            $cp->status = ($request->status ?? '0');
            $cp->save();

        }

        $project_id = empty($cp->id) ? $project->id : $cp->id;
        $checkProject = Recoveries::where('project_id', '=', $project_id)->count();

        if ($checkProject == 0) {
            $recoveries = new Recoveries();

            $recoveries->cid = (Auth::user()->cid ?? '');
            $recoveries->client_id = ($client_id ?? '');
            $recoveries->project_id = ($project_id ?? '');
            $recoveries->paid = ($request->received ?? '');
            $recoveries->note = ($request->note ?? '');
            $recoveries->reminder = $request->reminder ?? NOW();
            $recoveries->status = ($request->status ?? '0');

            if ($recoveries->save()) {
                return redirect('recoveries')->with('success', 'This recovery details was successfully added to the Recovery Table.');
            } else {
                return back()->with('error', 'Failed to add this Recovery to the Recovery table.');
            }
        } else {
            if (empty($cp->id)) {
                return back()->with('success', 'This recovery details was successfully added to the Recovery Table..');
            } else {
                return back()->with('success', 'This recovery details was successfully updated.');
            }
        }
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

    public function projects()
    {

        if (Auth::user()->role == 'master') {

            $clients = Clients::orderBy('id', 'DESC')->get();

        } else {

            $clients = Clients::where('cid', '=', Auth::user()->cid)->orderBy('status', 'ASC')->get();

        }

        return view('projects', ['clients' => $clients]);

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
            'license_key' => 'required|string|max:255|unique:eselicenses,eselicense_key,',
            'expiry_date' => 'nullable|date',
            'status' => 'required|in:active,blocked',
        ]);

        // Check for existing client
        $client = Clients::where('mob', $validatedData['mobile'])
            ->orWhere('email', $validatedData['email'])
            ->first();

        if (!$client) {
            $client = new Clients();
            $client->cid = 2;
            $client->name = $validatedData['name'];
            $client->company = $validatedData['company'];
            $client->email = $validatedData['email'];
            $client->mob = $validatedData['mobile'];
            $client->location = $validatedData['location'] ?? '';
            $client->status = '1';
            $client->save();
        } else {
            $client_id = $client->id;

            $client = Clients::findOrFail($client_id);
            ;
            $client->cid = 2;
            $client->name = $validatedData['name'];
            $client->company = $validatedData['company'];
            $client->email = $validatedData['email'];
            $client->mob = $validatedData['mobile'];
            $client->location = $validatedData['location'] ?? '';
            $client->status = '1';
            $client->update();
        }

        $client_id = $client->id;

        // Check if project exists or needs to be created
        if (empty($validatedData['project_id'])) {
            $project = new Projects();
            $project->cid = 2;
            $project->client_id = $client_id;
            $project->name = $validatedData['project_name'];
            $project->type = $validatedData['type'];
            $project->amount = $validatedData['cost'];
            $project->note = $validatedData['note'];
            $project->deployment_url = $validatedData['website'];
            $project->technology_stack = $validatedData['technology_stack'];
            $project->status = '1';
            $project->save();

            $project_id = $project->id;
        } else {
            $project_id = $validatedData['project_id'];
            $project = Projects::findOrFail($project_id);
            $project->cid = 2;
            $project->client_id = $client_id;
            $project->name = $validatedData['project_name'];
            $project->type = $validatedData['type'];
            $project->amount = $validatedData['cost'];
            $project->note = $validatedData['note'];
            $project->deployment_url = $validatedData['website'];
            $project->technology_stack = $validatedData['technology_stack'];
            $project->status = '1';
            $project->update();
        }

        $id = $request->id ?? '';
        // Save or update license
        if ($id) {
            $license = Eselicenses::findOrFail($id);
        } else {
            $license = new Eselicenses();
        }

        $license->eselicense_key = $validatedData['license_key'];
        $license->project_id = $project_id;
        $license->expiry_date = $validatedData['expiry_date'];
        $license->status = $validatedData['status'];
        $license->save();

        return redirect('licensing')->with('success', $id ? 'License updated successfully.' : 'License added successfully.');
    }

    public function clients()
    {

        if (Auth::user()->role == 'master') {

            $clients = Clients::orderBy('id', 'DESC')->get();

        } else {

            $clients = Clients::where('cid', '=', Auth::user()->cid)->orderBy('status', 'DESC')->get();

        }

        return view('clients', ['clients' => $clients]);

    }

    public function clientPost(Request $request)
    {

        if (Auth::user()->role == 'master') {

            $clients = Clients::orderBy('id', 'DESC')->get();

        } else {

            $clients = Clients::where('cid', '=', Auth::user()->cid)->orderBy('status', 'ASC')->get();

        }

        return view('clients', ['clients' => $clients]);

    }

    public function manageClient(Request $request)
    {

        $clients = Clients::where('id', '=', $request->id)->first();

        return view('manageClient', ['clients' => $clients]);

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

            if ($client->save()) {
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

            if ($leadSingle->update()) {
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

            $client = Clients::where('id', '=', $request->id)->first();

            $leadComments = Lead_comments::where('lead_id', '=', ($client->commentLeadID ?? ''))->get();

            return json_encode(['clients' => $client, 'leadComments' => $leadComments]);
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
}
