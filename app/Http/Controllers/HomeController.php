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

class HomeController extends Controller
{
    function index()
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
        $fromName = 'asdasd';

        $viewData = [
            'name' => $validatedData['name'], // Mapped from request
            'phone' => $validatedData['phone'],
            'email' => $validatedData['email'],
            'services' => $validatedData['services'],
            'messages' => $validatedData['message'],
        ];

        $subject = 'NEW ENQUIRY';
        $viewName = 'emails.welcome'; // You need to create this blade file
        $to = 'iwebbrella@gmail.com'; // Or an admin email, depending on logic

        // 5. Send Email using Laravel Mail Facade
        // try {
        $mailable = new CustomMailable(
            $subject,
            $viewName,
            $viewData,
            $fromAddress,
            $fromName
        );

        $m = Mail::to($to)->send($mailable);
        dd($m);
        // return back()->with('success', 'Proposal sent successfully!');

        // } catch (\Exception $e) {
        //     // Log the error for debugging
        //     \Log::error('Mail Send Error: ' . $e->getMessage());
        //     return back()->withErrors(['msg' => 'Message could not be sent. Please try again later.']);
        // }
    }
    public function home()
    {
        $auth_cid = Auth::user()->cid ?? '';
        $auth_uid = Auth::user()->id ?? '';

        // Existing Queries
        $users = User::where('cid', $auth_cid)->get();

        $newLeads = Leads::leftJoin('lead_comments', 'leads.id', '=', 'lead_comments.lead_id')
            ->where('leads.uid', $auth_uid)
            ->where('leads.status', 1)
            ->where('lead_comments.next_date', '<=', now())
            ->distinct()
            ->get(['leads.id']);

        $leads = Leads::where('cid', $auth_cid)->get();
        $clients = Clients::where('cid', $auth_cid)->get();

        $projects = \DB::table('projects')->where('cid', $auth_cid)->get();

        $todolists = Todo_lists::where('uid', $auth_uid)->orderBy('position')->get();

        $recoveries = Recoveries::select('project_id', \DB::raw('count(*) as total'))
            ->where('cid', $auth_cid)
            ->groupBy('project_id')
            ->get();

        /* --- REVENUE CHART LOGIC (Dynamic) --- */
        // Calculate monthly revenue for the current year
        $revenueData = Invoices::select(
            \DB::raw('SUM(total_amount) as total'),
            \DB::raw('MONTH(issue_date) as month')
        )
            ->where('cid', $auth_cid)
            ->whereYear('issue_date', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->all();

        // Fill missing months with 0
        $monthlyRevenue = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyRevenue[] = $revenueData[$i] ?? 0;
        }

        /* --- ACTIVITY MONITOR LOGIC --- */

        // 1. Get the raw activity flow for the company (Last 10 days) -- CORRECTED to use User-wise count if that's what the view wants
        // BUT wait, the view chart is labelled "Activity Monitor Flow (User-wise)" but the code was doing Date-wise. 
        // Let's stick to the VIEW'S INTENT which seems to be User-wise Contribution.

        // Calculate User Wise Counts for Chart (Global count, not just last 15)
        $userActivityCounts = \DB::table('activities')
            ->join('users', 'activities.user_id', '=', 'users.id')
            ->where('users.cid', $auth_cid)
            ->select('users.name', \DB::raw('count(*) as total'))
            ->groupBy('users.name')
            ->get();

        $activityChartLabels = $userActivityCounts->pluck('name')->toArray();
        $activityChartData = $userActivityCounts->pluck('total')->toArray();

        // 3. Get Recent Activity List for the Table
        $activities = \DB::table('activities')
            ->join('users', 'activities.user_id', '=', 'users.id')
            ->where('users.cid', $auth_cid)
            ->select('activities.*', 'users.name as user_name')
            ->orderBy('activities.created_at', 'DESC')
            ->limit(15)
            ->get();

        return view('home', [
            'users' => $users,
            'leads' => $leads,
            'newLeads' => $newLeads,
            'clients' => $clients,
            'projects' => $projects,
            'recoveries' => $recoveries,
            'todolists' => $todolists,
            'activities' => $activities,
            'activityChartLabels' => $activityChartLabels,
            'activityChartData' => $activityChartData,
            'monthlyRevenue' => $monthlyRevenue // Pass revenue data to view
        ]);
    }
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|string',
                'subject_id' => 'nullable|integer',
                'description' => 'nullable|string'
            ]);

            $validated['user_id'] = auth()->id(); // Or null if guest

            Activity::create($validated);

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            // Return error for debugging
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
