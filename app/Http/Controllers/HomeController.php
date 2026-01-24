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

class HomeController extends Controller
{
    function index(){
        return view('landingpg.index');
    }
    public function send(Request $request)
    {
        $validatedData = $request->validate([
            'name'    => 'required|string',
            'email'    => 'required|email',
            'phone'    => 'nullable|string',
            'services' => 'nullable|string',
            'message'  => 'required|string',
        ]);
            $fromAddress = 'website@creativekey.in'; 
            $fromName    = 'asdasd'; 
        
        $viewData = [
            'name'      => $validatedData['name'], // Mapped from request
            'phone'     => $validatedData['phone'],
            'email'     => $validatedData['email'],
            'services'  => $validatedData['services'],
            'messages'  => $validatedData['message'],
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
    public function home() {
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
        
        // Fixed: assuming $projects might be needed based on the Blade count($projects)
        $projects = \DB::table('projects')->where('cid', $auth_cid)->get(); 
        
        $todolists = Todo_lists::where('uid', $auth_uid)->orderBy('position')->get();
        
        $recoveries = Recoveries::select('project_id', \DB::raw('count(*) as total'))
            ->where('cid', $auth_cid)
            ->groupBy('project_id')
            ->get();
    
        /* --- ACTIVITY MONITOR LOGIC --- */
    
        // 1. Get the raw activity flow for the company (Last 10 days)
        // We join with users to ensure we only see activities from people in the same company
        $activityFlow = \DB::table('activities')
            ->join('users', 'activities.user_id', '=', 'users.id')
            ->where('users.cid', $auth_cid)
            ->select(\DB::raw('DATE(activities.created_at) as date'), \DB::raw('count(*) as aggregate'))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->take(10)
            ->get();
    
        // 2. Format data specifically for the Chart.js Labels and Data arrays
        $activityChartLabels = $activityFlow->pluck('date')->map(function($date) {
            return \Carbon\Carbon::parse($date)->format('d M');
        })->toArray();
    
        $activityChartData = $activityFlow->pluck('aggregate')->toArray();
    
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
            'activityChartData' => $activityChartData
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
