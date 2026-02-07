<?php

namespace App\Http\Controllers;

// External Packages
use Carbon\Carbon;
use Illuminate\Http\Request;

// Laravel Facades
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

// Application Specific Classes (Mail, Models, etc.)
use App\Mail\CustomMailable;
use App\Models\Companies;
use App\Models\Roles;
use App\Models\SmtpSettings;
use App\Models\User;
use App\Models\Eselicenses;

class AuthController extends Controller
{
    public function register()
    {
        return view('register');
    }

    public function registerPost(Request $request)
    {

        if ($request->captcha != $request->captcha_answer) {
            return back()->withErrors(['captcha' => 'Incorrect captcha answer.']);
        }

        try {

            $to = $request->reg_email ?? '';
            $subject = 'Welcome to Our ESECRM!';

            $message = "Thank you for registering with us. We are excited to have you on board!<br><br><b>Below are your panel login details:</b><br>
            <b>Username:</b> " . ($request->reg_email ?? '') . "<br>
            <b>Password:</b> " . ($request->reg_password ?? '') . "<br><br>
            If you have any questions or need assistance, feel free to reach out to our support team.<br><br>
            Thank you for your interest.<br><br>
            <b>Best regards,</b><br>Webbrella Global";

            $viewName = 'emails.welcome';
            $viewData = ["name" => ($request->reg_name ?? 'User'), "messages" => $message];

            $companies = new Companies();
            $companies->name = $request->reg_company ?? '';
            $companies->mob = $request->reg_mob ?? '';
            $companies->email = $request->reg_email ?? '';
            $companies->gst = $request->reg_gst ?? '';
            $companies->status = '1';
            $companies->save();

            $roles = new Roles();
            $roles->cid = $companies->id ?? '';
            $roles->title = 'Admin';
            $roles->subtitle = '';
            $roles->features = 'All';
            $roles->permissions = 'All';
            $roles->status = '1';
            $roles->save();

            $user = new User();
            $username = explode('@', $request->reg_email);
            $user->username = substr($request->reg_company, 0, 3) . $username[0];
            $user->name = $request->reg_name ?? '';
            $user->cid = $companies->id ?? '';
            $user->mob = $request->reg_mob ?? '';
            $user->email = $request->reg_email ?? '';
            $user->password = Hash::make($request->reg_password);
            $user->role = $roles->id ?? '';
            $user->save();

            $fromAddress = "info@esecrm.com"; // Get from DB if available
            $fromName = "eseCRM";       // Get from DB if available

            $mailable = new CustomMailable(
                $subject,
                $viewName,
                $viewData,
                $fromAddress, // Pass DB value or null
                $fromName     // Pass DB value or null
            );

            Mail::to($to)->send($mailable);

            return redirect('/login')->with('success', 'Successfully registered your business on our platform! To complete the setup, please verify your email and fill out your business profile to start reaching potential customers.');

            return back()->with('error', 'Oops, Somethings went worng.');

        } catch (Illuminate\Database\QueryException $e) {

            $errorCode = $e->errorInfo[1];

            if ($errorCode == 1062) {
                return back()->with('error', 'Duplicate Entry.');
            }

            return back()->with('error', 'Oops, Somethings went worng.');

        }

    }

    public function login()
    {
        return view('login');
    }

    public function loginPost(Request $request)
    {
        $credentials = [
            'email' => $request->login_email,
            'password' => $request->login_password,
        ];

        if (Auth::attempt($credentials)) {

            // Get the authenticated user
            $user = Auth::user();

            // Check if the user account is active
            if ($user->status != 1) {
                Auth::logout();
                return back()->with('error', 'Your account has been deactivated. Please contact the support team for assistance.');
            }

            // Retrieve related company and role information
            $company = Companies::find($user->cid);
            $role = Roles::find($user->role);

            // Store information in session
            session([
                'companies' => $company,
                'roles' => $role,
            ]);

            // Start PHP session if not already started and store credentials (not recommended for passwords)
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['loginEmail'] = $request->login_email ?? '';
            // Plaintext password storage removed for security.

            return redirect('/home')->with('success', 'Successfully logged in.');
        }

        return back()->with('error', 'Invalid login credentials.');
    }

    public function forgotPassword()
    {
        return view('forgotPassword');
    }

    public function forgotPasswordPost(Request $request)
    {

        $to = $request->forgot_email;

        $getUser = User::where('email', '=', $to)->first();

        if (!$getUser) {
            return back()->with('error', 'No user found with this email address.');
        }

        $getSociety = Companies::where('id', '=', $getUser->cid)->first();

        $subject = 'Reset Your Password for Your CRM Account';

        $message = "Dear " . $getUser->name . ",<br><br>
        We received a request to reset your password for your CRM account. If you did not make this request, please ignore this email. Otherwise, follow the instructions below to reset your password.<br><br>
        <b>Reset Your Password:</b><br>
        <ul>
            <li>Click on the following link or copy and paste it into your browser: <a href='https://esecrm.com/new-password?token=" . $getUser->id . "crm" . $getUser->password . "'>Password Reset Link</a></li>
            <li>Enter your new password in the provided field.</li>
            <li>Confirm your new password by re-entering it.</li>
            <li>Click the <b>Submit</b> button to complete the process.</li>
        </ul><br>
        For your security, this link will expire in 24 hours. If you need a new link, you can request another password reset through the Webbrella website.<br><br>
        Thank you for being a valued member of the Webbrella community!<br><br>
        <b>Best regards,</b><br>" . ($getSociety->name ?? 'ESECRM');

        $viewName = 'emails.welcome';
        $viewData = ["name" => $getUser->first_name, "messages" => $message];

        $smtpSettings = SmtpSettings::where('user_id', $getUser->id)->first();

        // 2. Fallback: If no user-specific settings found AND the user has a company ID (cid)
        if (!$smtpSettings && !empty($getUser->cid)) {
            $smtpSettings = SmtpSettings::where('cid', $getUser->cid)
                // ->whereNull('user_id') // Optional: Add if company settings have user_id = null
                ->first();
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

        return back()->with('success', 'Reset password link has been sent to your registered email address!');

    }

    public function newPassword(Request $request)
    {

        $token = explode('crm', ($request->token ?? ''));

        $getUser = User::where('id', '=', $token[0])->first();

        $id = $token[0] ?? '';

        if (!$getUser) {
            return back()->with('error', 'No user found with this email address.');
        }

        return view('newPassword', ['id' => $id]);

    }

    public function newPasswordPost(Request $request)
    {

        $id = $request->uid ?? '';
        $password = User::find($id);
        $password->password = Hash::make(($request->new_password ?? ''));
        $password->update();

        return redirect('login')->with('success', 'Your password has been successfully updated! You can now log in using your new password.');

    }

    public function logout(Request $request)
    {
        // 1. Clear specific custom session data you added during login
        session()->forget('companies');   // Or 'companies' if you stored the object
        session()->forget('roles');      // Or 'roles' if you stored the object
        //session()->forget('user_smtp_config'); // Crucial to clear SMTP config

        // 2. Log the user out from Laravel's authentication system
        Auth::logout();

        // 3. Invalidate the user's session.
        $request->session()->invalidate();

        // 4. Regenerate the CSRF token.
        $request->session()->regenerateToken();

        // 5. Redirect the user to the login page (or homepage)
        return redirect('/login') // Or route('login') if you use named routes
            ->with('info', 'You have been successfully logged out.'); // Use 'info' or 'success'
    }

    public function triggerCurl(Request $request)
    {

        $ese = Eselicenses::leftJoin('projects', 'eselicenses.project_id', '=', 'projects.id')
            ->select('projects.deployment_url', 'eselicenses.*')
            ->where('eselicenses.id', $request->id ?? '')
            ->first();

        // URL to trigger the action-core/index.php with cURL
        $url = ($ese->deployment_url ?? '') . 'vendor/coreoptions/index.php';

        // Data to send with the request 
        $data = [
            'status' => $request->status ?? '',  // Action to export database
            'token' => $ese->eselicense_key ?? '',
        ];

        // Make cURL POST request
        $response = $this->sendCurlRequest($url, $data);

        // Handle response
        return response()->json($response);
    }

    private function sendCurlRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        // Decode JSON response
        return json_decode($response, true);
    }
}
