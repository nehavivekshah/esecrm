<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\AuthController; 

use App\Models\Companies;
use App\Models\User;
use App\Models\Roles;
use App\Models\Attendances;
use App\Models\Holidays;
use Carbon\Carbon;

class UserController extends Controller
{
    public function attendances(Request $request)
    {
        $user = Auth::user();
        $roles = session('roles');
        $isAdmin = $roles && $roles->title === 'Admin';
    
        // Filters
        $selectedUserId = $request->input('user_id');
        $range = $request->input('range', $isAdmin ? 'today' : '7days');
        $today = now()->toDateString();
        $dates = collect();
        
        switch ($range) {
            case 'today':
                $dates->push($today);
                break;
        
            case '7days':
                for ($i = 0; $i < 7; $i++) {
                    $dates->push(now()->subDays($i)->toDateString());
                }
                break;
        
            case 'month': // This month
                $start = now()->startOfMonth();
                $end   = now();
                while ($start <= $end) {
                    $dates->push($start->toDateString());
                    $start->addDay();
                }
                break;
        
            case 'last-month':
                $start = now()->subMonth()->startOfMonth();
                $end   = now()->subMonth()->endOfMonth();
                while ($start <= $end) {
                    $dates->push($start->toDateString());
                    $start->addDay();
                }
                break;
        
            case 'year': // Current year till today
                $start = now()->startOfYear();
                $end   = now();
                while ($start <= $end) {
                    $dates->push($start->toDateString());
                    $start->addDay();
                }
                break;
        
            default:
                $dates->push($today);
        }

    
        // Load users
        if ($isAdmin) {
            $users = User::select('id', 'name', 'working_times')
                ->where('cid', $user->cid)
                ->when($selectedUserId, fn($q) => $q->where('id', $selectedUserId))
                ->get();
        } else {
            $users = collect([$user]);
        }
    
        $userIds = $users->pluck('id')->toArray();
    
        // Attendance data for all users in selected range
        $attendanceData = Attendances::whereIn('date', $dates)
            ->whereIn('user_id', $userIds)
            ->get()
            ->groupBy('user_id');
    
        $holidays = Holidays::whereIn('date', $dates)->get()->keyBy('date');
    
        $final = [];
        $summary = [
            'working_days' => 0,
            'expected_hours' => 0,
            'worked_hours' => 0,
            'holidays' => 0,
            'leaves' => 0,
            'present' => 0,
            'absent' => 0,
        ];
    
        foreach ($users as $u) {
            $uid = $u->id;
            $userAttendance = $attendanceData->has($uid) ? $attendanceData[$uid]->keyBy('date') : collect();
            $workingTimes = json_decode($u->working_times, true);
            $start = $workingTimes['start'] ?? '10:00';
            $end = $workingTimes['end'] ?? '18:00';
            $expectedHours = \Carbon\Carbon::parse($end)->diffInHours(\Carbon\Carbon::parse($start));
    
            foreach ($dates as $date) {
                $dayName = \Carbon\Carbon::parse($date)->format('l');
                $checkIn = $checkOut = $method = $remarks = '-';
                $workedHours = 0;
                $type = 'Working Day';
                $status = 'Absent';
    
                if ($userAttendance->has($date)) {
                    $att = $userAttendance[$date];
                    $checkIn = $att->check_in ?? '-';
                    $checkOut = $att->check_out ?? '-';
                    $method = $att->method ?? '-';
                    $remarks = $att->remarks ?? '-';
                    $status = $att->status;
    
                    if ($checkIn !== '-' && $checkOut !== '-') {
                        //$workedHours = \Carbon\Carbon::parse($checkOut)->diffInHours(\Carbon\Carbon::parse($checkIn));
                        $minutes = Carbon::parse($checkOut)->diffInMinutes(Carbon::parse($checkIn));
                        $workedHours = round($minutes / 60, 2);
                    }
                } elseif (isset($holidays[$date])) {
                    $status = 'Holiday';
                    $type = 'Holiday: ' . $holidays[$date]->title;
                } elseif ($dayName === 'Sunday') {
                    $status = 'Holiday';
                    $type = 'Sunday';
                } else {
                    $status = 'Leave';
                    $type = 'Leave';
                }
    
                // Summary updates
                if ($status === 'Present') $summary['present']++;
                if ($status === 'Leave') $summary['leaves']++;
                if ($status === 'Absent') $summary['absent']++;
                if ($status === 'Holiday') $summary['holidays']++;
    
                if (!in_array($status, ['Holiday'])) {
                    $summary['working_days']++;
                    $summary['expected_hours'] += $expectedHours;
                    $summary['worked_hours'] += $workedHours;
                }
    
                $final[] = [
                    'user' => $u->name,
                    'user_id' => $uid,
                    'date' => $date,
                    'day' => $dayName,
                    'status' => $status,
                    'type' => $type,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'method' => $method,
                    'remarks' => $remarks,
                    'expected_hours' => $expectedHours,
                    'worked_hours' => $workedHours,
                ];
            }
        }
    
        return view('attendances', compact('final', 'isAdmin', 'users', 'summary', 'range', 'selectedUserId'));
    }

    //User Controller
    function users(Request $request){
        
        $segment = $request->segment(1);
        $roles = session('roles');
        
        if(Auth::user()->role == 'master'){
        
            $users = User::leftjoin('companies','users.cid','=','companies.id')
                ->leftjoin('roles','users.role','=','roles.id')
                ->select('companies.name','roles.title','roles.subtitle','users.*')->get();
            
            $roles = Roles::where('cid','=',(Auth::user()->cid ?? ''))->get();
        
        }elseif($segment == 'admins'){
            
            $users = User::leftjoin('companies','users.cid','=','companies.id')
                ->leftjoin('roles','users.role','=','roles.id')
                ->select('companies.name','roles.title','roles.subtitle','users.*')
                ->where('users.cid','=',(Auth::user()->cid ?? ''))
                ->where('roles.features','=','All')->get();
            
            $roles = Roles::where('cid','=',(Auth::user()->cid ?? ''))->get();
            
        }elseif($segment == 'employees'){
            
            $users = User::leftjoin('companies','users.cid','=','companies.id')
                ->leftjoin('roles','users.role','=','roles.id')
                ->select('companies.name','roles.title','roles.subtitle','users.*')
                ->where('users.cid','=',(Auth::user()->cid ?? ''))
                ->where('roles.features','!=','All')->get();
            
            $roles = Roles::where('cid','=',(Auth::user()->cid ?? ''))->get();
            
        }else{
            
            $users = User::leftjoin('companies','users.cid','=','companies.id')
                ->leftjoin('roles','users.role','=','roles.id')
                ->select('companies.name','roles.title','roles.subtitle','users.*')
                ->where('users.cid','=',(Auth::user()->cid ?? ''))->get();
            
            $roles = Roles::where('cid','=',(Auth::user()->cid ?? ''))->get();
            
        }
        
        return view('users',['users'=>$users]);
    }
    
    function manageUser(Request $request){
        
        $segment = $request->segment(1);
        
        if($segment == 'my-profile'){ $uid = Auth::user()->id ?? ''; }else{ $uid = $request->id ?? ''; }
        
        $users = User::leftjoin('companies','users.cid','=','companies.id')
            ->leftjoin('roles','users.role','=','roles.id')
            ->select('companies.name as company','companies.img','roles.title','roles.features as roleFeatures','users.*')
            ->where('users.id','=',$uid)->first();
            
        $allusers = User::leftjoin('companies','users.cid','=','companies.id')
                ->leftjoin('roles','users.role','=','roles.id')
                ->select('companies.name','roles.title','roles.subtitle','users.*')
                ->where('users.cid','=',(Auth::user()->cid ?? ''))->get();
            
        $roles = Roles::where('cid','=',(Auth::user()->cid ?? ''))->where('features','!=','All')->get();
        
        return view('manageUser',['users'=>$users,'roles'=>$roles,'allusers'=>$allusers]);
    }
    
    function manageUserPost(Request $request){
        
        $assign = implode(',',($request->assign ?? []));
        $features = implode(',',($request->features ?? []));
        
        if(empty($request->id)){
            
            $user = new User();
            
            $username = explode('@',$request->email);
            
            $user->cid = (Auth::user()->cid ?? '');
            $user->username = $username[0].substr($request->mob,0,3);
            $user->name = ($request->name ?? '');
            $user->email = ($request->email ?? '');
            $user->mob = ($request->mob ?? '');
            if(!empty($request->password)){
            $user->password = Hash::make($request->password);
            }
            
            if(!empty($request->file('profilePhoto'))):
                
                // $request->validate([
                //     'image' => 'required|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
                // ]);
                $fileName = time().".".$request->profilePhoto->extension();
                $request->profilePhoto->move(public_path("/assets/images/profile"), $fileName);

            endif;

            $user->photo = $fileName ?? '';
            
            if(!empty($request->file('imgsign'))):
                
                // $request->validate([
                //     'image' => 'required|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
                // ]);
                $fileName1 = time().".".$request->imgsign->extension();
                $request->imgsign->move(public_path("/assets/images/signs"), $fileName1);
                
                $user->imgsign = $fileName1 ?? '';

            endif;
            
            $user->role = ($request->role ?? '');
            $user->assign = $assign;
            $user->working_times = json_encode($request->time ?? []);
            $user->features = $features;
            $user->esign = ($request->emailSign ?? '');
            $user->status = ($request->status ?? '');
            
            $user->save();
            
            return redirect('manage-user')->with('success', 'New user role was successfully added.');
            
            return redirect('manage-user')->with('error', 'Opps! Something has gone wrong.');
            
        }else{
            
            $id = $request->id ?? '';
            
            $user = User::find($id);
            
            $user->cid = (Auth::user()->cid ?? '');
            $user->name = ($request->name ?? '');
            $user->email = ($request->email ?? '');
            $user->mob = ($request->mob ?? '');
            if(!empty($request->password)){
                $user->password = Hash::make($request->password);
            }
            
            if(!empty($request->file('profilePhoto'))):
                
                // $request->validate([
                //     'image' => 'required|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
                // ]);
                $fileName = time().".".$request->profilePhoto->extension();
                $request->profilePhoto->move(public_path("/assets/images/profile"), $fileName);
                
                $user->photo = $fileName ?? '';

            endif;
            
            if(!empty($request->file('imgsign'))):
                
                // $request->validate([
                //     'image' => 'required|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
                // ]);
                $fileName1 = time().".".$request->imgsign->extension();
                $request->imgsign->move(public_path("/assets/images/signs"), $fileName1);
                
                $user->imgsign = $fileName1 ?? '';

            endif;
            
            if(!empty($request->role)){
                $user->role = ($request->role ?? '');
            }
            
            $user->assign = $assign;
            $user->working_times = json_encode($request->time ?? []);
            $user->features = $features;
            $user->esign = ($request->emailSign ?? '');
            $user->status = ($request->status ?? '');
            
            $user->update();
            
            if(!empty($request->file('companyLogo'))):
                
                $cid = (Auth::user()->cid ?? '');
                
                $company = Companies::find($cid);
                
                // $request->validate([
                //     'image' => 'required|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
                // ]);
                $fileName = time().".".$request->companyLogo->extension();
                $request->companyLogo->move(public_path("/assets/images/company"), $fileName);
                
                $company->img = $fileName ?? '';
                
                $company->update();

            endif;
            
            return back()->with('success', 'Successfully updated.');
            
            return back()->with('error', 'Opps! Something has gone wrong.');
            
        }
        
    }
    
    //Company Controller
    function companies(Request $request){
        
        $companies = Companies::get();
        
        return view('companies',['companies'=>$companies]);
    }
    function manageCompany(Request $request){
        
        $segment = $request->segment(1);
        
        if($segment == 'my-company'){ $cid = Auth::user()->cid ?? ''; }else{ $cid = $request->id ?? ''; }
        
        $companies = Companies::where('id','=',$cid)->first();
        
        return view('manageCompany',['company'=>$companies]);
    }
    public function manageCompanyPost(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mob' => 'nullable|string|max:15',
            'gst' => 'nullable|string|max:20',
            'vat' => 'nullable|string|max:20',
            'tax_rates' => 'nullable|array',
            'tax_rates.*' => 'numeric',
            'bank_details' => 'nullable|array',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zipcode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'subscription' => 'nullable|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'pdf_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        $taxRates = implode(',', $request->tax_rates ?? []);
        $bankDetails = json_encode($request->bank_details ?? []);
        
        $segment = $request->segment(1);
        //dd($segment);
        if(!empty($request->id)){ $id = $request->id ?? ''; }else{ $id = Auth::user()->cid ?? ''; }
        
        //dd($id);
        
        if (empty($id)) {
            $company = new Companies();
        } else {
            $company = Companies::find($id);
            if (!$company) {
                return back()->with('error', 'Company not found.');
            }
        }
        
        $company->name = $request->name;
        $company->email = $request->email;
        $company->mob = $request->mob;
        $company->gst = $request->gst;
        $company->vat = $request->vat;
        $company->tax = $taxRates;
        $company->bank_details = $bankDetails;
        $company->address = $request->address;
        $company->city = $request->city;
        $company->state = $request->state;
        $company->zipcode = $request->zipcode;
        $company->country = $request->country;
        if(!empty($request->id)){
        $company->plan = $request->subscription ?? 'standard';
        }
        if ($request->hasFile('logo')) {
            $fileName = time().'.'.$request->logo->extension();
            $request->logo->move(public_path('/assets/images/company/logos'), $fileName);
            $company->logo = $fileName;
        }
        if ($request->hasFile('pdf_logo')) {
            $fileName = time().'.'.$request->pdf_logo->extension();
            $request->pdf_logo->move(public_path('/assets/images/company'), $fileName);
            $company->pdf_logo = $fileName;
        }
        
        $company->save();
        
        return back()->with('success', 'Company details successfully saved.');
    }
    
    //Reset Password Controller
    function resetPassword(){
        return view('resetPassword');
    }
    function resetPasswordPost(Request $request){
        
        $id = Auth::user()->id ?? '';
            
        $user = User::find($id);
        $user->password = Hash::make($request->cn_password);
        
        $user->update();
        
        return redirect('reset-password')->with('success', 'Successfully updated.');
            
        return redirect('reset-password')->with('error', 'Opps! Something has gone wrong.');
    }
}
