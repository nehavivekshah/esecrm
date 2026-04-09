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
    protected $userService;

    public function __construct(\App\Services\UserService $userService)
    {
        $this->userService = $userService;
    }

    public function attendances(Request $request)
    {
        $user = Auth::user();
        $roles = session('roles');
        $isAdmin = $roles && $roles->title === 'Admin';
    
        $selectedUserId = $request->input('user_id');
        $range = $request->input('range', $isAdmin ? 'today' : '7days');

        $data = $this->userService->getAttendanceReport($user, $isAdmin, $selectedUserId, $range);

        return view('attendances', array_merge($data, [
            'isAdmin' => $isAdmin,
            'range' => $range,
            'selectedUserId' => $selectedUserId
        ]));
    }

    //User Controller
    public function users(Request $request)
    {
        $segment = $request->segment(1);
        $user = Auth::user();

        $users = $this->userService->getUsersBySegment($user, $segment);
        // Note: The original code fetched roles but didn't pass them to the 'users' view. 
        // We'll keep the view call standard. If roles are needed in view later, add them here.
        
        return view('users', ['users' => $users]);
    }
    
    public function manageUser(Request $request)
    {
        $segment = $request->segment(1);
        $uid = ($segment == 'my-profile') ? Auth::id() : $request->id;
        
        $data = $this->userService->getUserDetails($uid, Auth::user());
        
        return view('manageUser', $data);
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
        }
        
    }
    
    //Company Controller
    function companies(Request $request){
        $status = $request->input('status');
        $query = Companies::query();
        if($status !== null && $status !== ''){
            $query->where('status', $status);
        }
        $companies = $query->get();
        
        return view('companies', [
            'companies' => $companies,
            'status' => $status
        ]);
    }
    function manageCompany(Request $request){
        
        $segment = $request->segment(1);
        
        if($segment == 'my-company'){ $cid = Auth::user()->cid ?? ''; }else{ $cid = $request->id ?? ''; }
        
        $companies = Companies::where('id','=',$cid)->first();
        
        $viewData = ['company'=>$companies];

        if ($request->has('ajax')) {
            return view('manageCompanyForm', $viewData);
        }
        
        return view('manageCompany', $viewData);
    }
    function viewCompany(Request $request){
        $cid = $request->id ?? '';
        $company = Companies::find($cid);
        if (!$company) {
            return response('<div class="p-5 text-center text-danger">Company not found.</div>', 404);
        }
        return view('viewCompanyForm', ['company' => $company]);
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
            'img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
        if ($request->hasFile('img')) {
            $fileName = time().'.'.$request->img->extension();
            $request->img->move(public_path('/assets/images/company'), $fileName);
            $company->img = $fileName;
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
    }
}
