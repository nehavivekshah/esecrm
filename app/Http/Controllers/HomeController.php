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

    public function index()
    {
        return view('landingpg.index');
    }

    public function send(Request $request)
    {
        // ... (Send logic is fine, but I need to make sure I don't delete it if I am replacing the whole file content or a large chunk)
        // actually the previous view_file showed lines 1-99 and it was VERY truncated.
        // It seems the previous replace_file_content REPLACED lines 16-177 with a truncated version because I didn't provide the full content in ReplacementContent? 
        // No, I provided a lot of content but maybe I missed the middle part.
        
        // Let's just fix the `home` method and `store` method.
        // The previous file content shows `index`, `send` (truncated in view?), and `home` (truncated) and `store`.
        
        // I will replace from line 22 to the end of the file with the CORRECT full content.
        
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
        $m = Mail::to($to)->send($mailable);
        dd($m);
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

        /* --- DASHBOARD WIDGETS DATA --- */
        $outstandingInvoices = Invoices::where('cid', $auth_cid)->where('status', '!=', 'Paid')->sum('total_amount');
        $pendingProposals = Proposals::where('cid', $auth_cid)->whereIn('status', ['Open', 'Sent'])->count();
        $myPendingTasks = Task::where('uid', $auth_uid)->where('status', '!=', '4')->count();
        $totalLeads = Leads::where('cid', $auth_cid)->count();

        /* --- REVENUE CHART LOGIC (Dynamic) --- */
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

        $monthlyRevenue = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyRevenue[] = $revenueData[$i] ?? 0;
        }

        /* --- ACTIVITY MONITOR LOGIC --- */
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
            'monthlyRevenue' => $monthlyRevenue,
            'outstandingInvoices' => $outstandingInvoices,
            'pendingProposals' => $pendingProposals,
            'myPendingTasks' => $myPendingTasks,
            'totalLeads' => $totalLeads
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

            $validated['user_id'] = auth()->id(); 

            Activity::create($validated);

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
