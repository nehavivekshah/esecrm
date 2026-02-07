<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Leads;
use App\Models\Clients;
use App\Models\Recoveries;
use App\Models\Todo_lists;
use App\Mail\CustomMailable;
use Illuminate\Support\Facades\Mail;
use App\Models\SmtpSettings;
use App\Models\Activity;
use App\Models\Invoices;
use App\Models\Proposals;
use App\Models\Task;

class HomeController extends Controller
{
    public function index()
    {
        return view('landingpg.index');
    }

    public function send(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'services' => 'nullable|string',
            'message' => 'required|string',
        ]);
        $fromAddress = 'website@creativekey.in';
        $fromName = 'Introduction';

        $viewData = [
            'name' => $validatedData['name'],
            'phone' => $validatedData['phone'],
            'email' => $validatedData['email'],
            'services' => $validatedData['services'],
            'messages' => $validatedData['message'],
        ];

        $subject = 'NEW ENQUIRY';
        $viewName = 'emails.welcome';
        $to = 'iwebbrella@gmail.com';

        $mailable = new CustomMailable($subject, $viewName, $viewData, $fromAddress, $fromName);
        Mail::to($to)->send($mailable);

        return back()->with('success', 'Thank you for contacting us. We will get back to you soon.');
    }

    public function home()
    {
        $auth_cid = Auth::user()->cid ?? '';
        $auth_uid = Auth::user()->id ?? '';

        // Basic Counts and Lists
        $users = User::where('cid', $auth_cid)->get();
        $leads = Leads::where('cid', $auth_cid)->get();
        $clients = Clients::where('cid', $auth_cid)->get();
        $projects = DB::table('projects')->where('cid', $auth_cid)->get();
        $recoveries = Recoveries::where('cid', $auth_cid)->get();
        $todolists = Todo_lists::where('uid', $auth_uid)->orderBy('position', 'DESC')->get();

        // New Lead Notification Logic
        $newLeads = Leads::leftJoin('lead_comments', 'leads.id', '=', 'lead_comments.lead_id')
            ->where('leads.uid', $auth_uid)
            ->where('leads.status', 1)
            ->where('lead_comments.next_date', '<=', now())
            ->distinct()
            ->get(['leads.id']);

        /* --- DASHBOARD WIDGETS DATA --- */
        $outstandingInvoices = Invoices::where('cid', $auth_cid)->where('status', '!=', 'Paid')->sum('total_amount');
        $pendingProposals = Proposals::where('cid', $auth_cid)->whereIn('status', ['Open', 'Sent'])->count();
        $myPendingTasks = Task::where('uid', $auth_uid)->where('status', '!=', '4')->count();
        $totalLeads = Leads::where('cid', $auth_cid)->count();

        /* --- REVENUE CHART LOGIC (Line Chart) --- */
        $revenueDataRaw = Invoices::select(
            DB::raw('SUM(total_amount) as total'),
            DB::raw('MONTH(invoice_date) as month')
        )
            ->where('cid', $auth_cid)
            ->whereYear('invoice_date', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        $monthlyRevenue = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyRevenue[] = (float) ($revenueDataRaw[$i] ?? 0);
        }

        /* --- ACTIVITY MONITOR LOGIC (Bar Chart) --- */
        $userActivityCounts = DB::table('activities')
            ->join('users', 'activities.user_id', '=', 'users.id')
            ->where('users.cid', $auth_cid)
            ->select('users.name', DB::raw('count(*) as total'))
            ->groupBy('users.name')
            ->get();

        $activityChartLabels = $userActivityCounts->pluck('name')->toArray();
        $activityChartData = $userActivityCounts->pluck('total')->toArray();

        // Recent Activity List for the Table
        $activities = DB::table('activities')
            ->join('users', 'activities.user_id', '=', 'users.id')
            ->where('users.cid', $auth_cid)
            ->select('activities.*', 'users.name as user_name')
            ->orderBy('activities.created_at', 'DESC')
            ->limit(15)
            ->get();

        return view('home', compact(
            'users',
            'leads',
            'newLeads',
            'clients',
            'projects',
            'recoveries',
            'todolists',
            'outstandingInvoices',
            'pendingProposals',
            'myPendingTasks',
            'totalLeads',
            'monthlyRevenue',
            'activities',
            'activityChartLabels',
            'activityChartData'
        ));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'type' => 'required|string',
            'subject_id' => 'nullable|integer',
            'description' => 'required|string',
            'value' => 'nullable|string',
        ]);

        $activity = new Activity();
        $activity->user_id = auth()->id();
        $activity->type = $validatedData['type'];
        $activity->subject_id = $validatedData['subject_id'];
        $activity->description = $validatedData['description'];
        $activity->value = $validatedData['value'];
        $activity->save();

        return response()->json(['success' => 'Activity logged successfully.']);
    }
}
