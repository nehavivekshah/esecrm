<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AjaxController;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\NewLeadController;
use App\Http\Controllers\SchedulerTestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::post('/activities/store', [HomeController::class, 'store']);

/*Proposal Actions*/
Route::get('/proposal/{id}/{token}', [LeadController::class, 'proposal']);
Route::get('/proposal/{id}/{token}/download', [LeadController::class, 'downloadPdf'])->name('proposal.download');
Route::post('/proposal/{id}/{token}/accept', [LeadController::class, 'acceptProposal'])->name('proposal.accept');
Route::post('/proposal/{id}/{token}/decline', [LeadController::class, 'declineProposal'])->name('proposal.decline');
Route::get('/', [HomeController::class, 'index']);
Route::post('/send', [HomeController::class, 'send']);
Route::group(['middleware' => 'guest'], function () {
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'registerPost'])->name('register');
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'loginPost'])->name('login');
    Route::get('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPasswordPost'])->name('forgotPassword');

    Route::get('/new-password', [AuthController::class, 'newPassword'])->name('newPassword');
    Route::post('/new-password', [AuthController::class, 'newPasswordPost'])->name('newPassword');

    Route::get('/export-lead-all', [LeadController::class, 'exportAllLeads'])->name('exportAllLeads');
    Route::get('/reminders', [LeadController::class, 'reminderScript'])->name('reminderScript');

});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/home', [HomeController::class, 'home']);



    // Routes for managing todo list items
    Route::get('/todo-lists', [TaskController::class, 'index']); // Fetch all tasks
    Route::post('/manage-todolist-item', [TaskController::class, 'store']); // Create new task
    Route::put('/manage-todolist-item/{id}', [TaskController::class, 'update']); // Update task completion
    Route::post('/todo-lists/reorder', [TaskController::class, 'reorder']); // Reorder tasks
    Route::delete('/manage-todolist-item/{id}', [TaskController::class, 'destroy']); // Delete a task
    Route::delete('/manage-todolist-item/clear', [TaskController::class, 'clearAll']); // Clear all tasks
    Route::post('/save-token', [TaskController::class, 'saveToken']); // Save FCM token

    Route::get('/firebase-messaging-sw.js', function () {
        $content = "importScripts('https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js');
    importScripts('https://www.gstatic.com/firebasejs/8.10.0/firebase-messaging.js');
    
    firebase.initializeApp({
        apiKey: '" . env('FIREBASE_API_KEY') . "',
        authDomain: '" . env('FIREBASE_AUTH_DOMAIN') . "',
        projectId: '" . env('FIREBASE_PROJECT_ID') . "',
        storageBucket: '" . env('FIREBASE_STORAGE_BUCKET') . "',
        messagingSenderId: '" . env('FIREBASE_MESSAGING_SENDER_ID') . "',
        appId: '" . env('FIREBASE_APP_ID') . "',
        measurementId: '" . env('FIREBASE_MEASUREMENT_ID') . "'
    });
    
    const messaging = firebase.messaging();
    
    messaging.onBackgroundMessage(function(payload) {
        console.log('[firebase-messaging-sw.js] Received background message ', payload);
        const notificationTitle = payload.notification.title;
        const notificationOptions = {
            body: payload.notification.body,
            icon: '/favicon.ico'
        };
    
        self.registration.showNotification(notificationTitle,
            notificationOptions);
    });";
        return response($content)->header('Content-Type', 'application/javascript');
    });



    /*Task Managment Router*/
    Route::get('/task', [TaskController::class, 'task']);
    Route::post('/task', [TaskController::class, 'taskPost'])->name('task');
    Route::get('/edit-task', [TaskController::class, 'taskEdit'])->name('edit-task');

    Route::controller(TaskController::class)->group(function () {
        Route::get('/tasksubmit', 'tasksubmit')->name('tasksubmit');
        Route::post('/tasksubmit', 'tasksubmit')->name('tasksubmit');
    });



    /*Leads Management Router*/
    Route::get('/leads', [LeadController::class, 'leads']);
    Route::get('/view-single-lead', [LeadController::class, 'singleLeadsGet'])->name('singleLead');

    /* Assign Leads Router*/
    Route::get('/leads-list', [LeadController::class, 'leadList']);
    Route::post('/leads', [LeadController::class, 'leadsPost'])->name('leads');

    Route::get('/newleads', [NewLeadController::class, 'newleads'])->name('leads.index');
    Route::post('/bulk-assign-leads', [NewLeadController::class, 'bulkAssign'])->name('leads.bulkAssign');

    Route::get('/get-lead-details/{id}', [NewLeadController::class, 'getLeadDetails']);
    Route::get('/leads/update-profile', [NewLeadController::class, 'updateLead'])->name('leads.update');
    Route::post('/leads/store-comment', [NewLeadController::class, 'storeComment'])->name('leads.storeComment');
    Route::post('/delete-lead', [NewLeadController::class, 'deleteLead'])->name('leads.delete');

    /*Manage Lead Data*/
    Route::get('/manage-lead', [LeadController::class, 'manageLead'])->name('manageLead');
    Route::post('/manage-lead', [LeadController::class, 'manageLeadPost'])->name('manageLead');

    //Route::get('/get-lead-data', [LeadController::class, 'getLeadData']);

    /*Import & export Leads Data Router*/
    Route::post('/import-leads-file', [LeadController::class, 'importLeads'])->name('importLeads');
    Route::get('/export-leads-file', [LeadController::class, 'exportLeads'])->name('exportLeads');

    /*Leads Comments Management Router*/
    Route::get('/lead-comments', [LeadController::class, 'leadComments']);
    Route::get('/manage-lead-comment', [LeadController::class, 'manageLeadComment'])->name('manageLeadComment');
    Route::post('/manage-lead-comment', [LeadController::class, 'manageLeadCommentPost'])->name('manageLeadComment');



    /*Manage Proposal Router*/
    Route::get('/proposals', [LeadController::class, 'proposals']);
    Route::get('/manage-proposal', [LeadController::class, 'manageProposal'])->name('manageProposal');
    Route::post('/manage-proposal', [LeadController::class, 'manageProposalPost'])->name('manageProposal');


    /*Proposal Actions*/
    Route::get('/quotation/{id}/{token}', [LeadController::class, 'proposal']);
    Route::get('/quotation/{id}/{token}/download', [LeadController::class, 'downloadPdf'])->name('proposal.download');
    Route::post('/quotation/{id}/{token}/accept', [LeadController::class, 'acceptProposal'])->name('proposal.accept');
    Route::post('/quotation/{id}/{token}/decline', [LeadController::class, 'declineProposal'])->name('proposal.decline');


    /*Clients Management Router*/
    Route::get('/clients', [ClientController::class, 'clients']);
    Route::get('/get-client/{clientId}', [ClientController::class, 'getClient']);
    Route::get('/clients-list', [ClientController::class, 'clientList']);
    Route::post('/clients', [ClientController::class, 'clientsPost'])->name('clients');
    Route::get('/view-single-client', [ClientController::class, 'singleClientGet'])->name('singleClient');
    Route::get('/manage-client', [ClientController::class, 'manageClient'])->name('manageClient');
    Route::post('/manage-client', [ClientController::class, 'manageClientPost'])->name('manageClient');

    /*Client Comments Management Router*/
    Route::get('/client-comments', [LeadController::class, 'clientComments']);
    Route::get('/manage-client-comment', [LeadController::class, 'manageClientComment'])->name('manageClientComment');
    Route::post('/manage-client-comment', [LeadController::class, 'manageClientCommentPost'])->name('manageClientComment');



    /*Recoveries's Account Management Router*/
    Route::get('/recoveries', [ClientController::class, 'recoveries']);
    Route::get('/manage-recovery', [ClientController::class, 'manageRecovery'])->name('manageRecovery');
    Route::post('/manage-recovery', [ClientController::class, 'manageRecoveryPost'])->name('manageRecovery');
    Route::get('/recovery/{id}/{title}', [ClientController::class, 'recovery'])->name('recovery');
    Route::post('/recovery', [ClientController::class, 'recoveryPost'])->name('recovery');
    Route::get('/update-recovery-amount', [ClientController::class, 'updateRecoveryAmount'])->name('recovery');
    Route::get('/delete-recovery-amount', [AjaxController::class, 'ajaxSend']);



    /*Project's Account Management Router*/
    Route::get('/projects', [ClientController::class, 'projects']);
    Route::get('/get-projects/{clientId}', [ClientController::class, 'getProjects']);
    Route::get('/manage-project', [ClientController::class, 'manageProject'])->name('manageProject');
    Route::post('/manage-project', [ClientController::class, 'manageProjectPost'])->name('manageProject');



    /*Contract's Account Management Router*/
    Route::get('/contracts', [ClientController::class, 'contracts']);
    Route::get('/manage-contract', [ClientController::class, 'manageContract'])->name('manageContract');
    Route::post('/manage-contract', [ClientController::class, 'manageContractPost'])->name('manageContract');



    /*Manage Licensing Router*/
    Route::get('/licensing', [ClientController::class, 'licensing']);
    Route::get('/manage-license', [ClientController::class, 'manageLicense'])->name('manageLicense');
    Route::post('/manage-license', [ClientController::class, 'manageLicensePost'])->name('manageLicense');



    /*Invoice's Router*/
    Route::get('/invoices', [ClientController::class, 'invoices']);
    Route::get('/invoices/preview/{id}', [ClientController::class, 'invoicePreview'])->name('invoicePreview');
    Route::get('/invoices/pdf/preview/{id}', [ClientController::class, 'invoicePdfPreview'])->name('invoicePdfPreview');
    Route::get('/invoices/download/{id}', [ClientController::class, 'invoiceDownload'])->name('invoiceDownload');
    Route::get('/manage-invoice', [ClientController::class, 'manageInvoice'])->name('manageInvoice');
    Route::post('/manage-invoice', [ClientController::class, 'manageInvoicePost'])->name('manageInvoice');
    Route::post('/manage-invoice-client', [ClientController::class, 'manageInvoiceClientPost']);



    /*User's Attendances Management Router*/
    Route::get('/attendances', [UserController::class, 'attendances']);
    Route::get('/manage-attendance', [UserController::class, 'manageAttendance'])->name('manageAttendance');
    Route::post('/manage-attendance', [UserController::class, 'manageAttendancePost'])->name('manageAttendance');



    /*User's Account Management Router*/
    Route::get('/users', [UserController::class, 'users']);
    Route::get('/manage-user', [UserController::class, 'manageUser'])->name('manageUser');
    Route::post('/manage-user', [UserController::class, 'manageUserPost'])->name('manageUser');



    /*Companies Management Router*/
    Route::get('/companies', [UserController::class, 'companies']);
    Route::get('/manage-company', [UserController::class, 'manageCompany'])->name('manageCompany');
    Route::post('/manage-company', [UserController::class, 'manageCompanyPost'])->name('manageCompany');

    /*Admin's Account Management Router*/
    Route::get('/admins', [UserController::class, 'users']);
    Route::get('/manage-admin', [UserController::class, 'manageUser'])->name('manageUser');
    Route::post('/manage-admin', [UserController::class, 'manageUserPost'])->name('manageUser');

    /*Employee's Account Management Router*/
    Route::get('/employees', [UserController::class, 'users']);
    Route::get('/manage-employee', [UserController::class, 'manageUser'])->name('manageUser');
    Route::post('/manage-employee', [UserController::class, 'manageUserPost'])->name('manageUser');

    /*My Profile Management Router*/
    Route::get('/my-profile', [UserController::class, 'manageUser']);
    Route::post('/my-profile', [UserController::class, 'manageUserPost'])->name('manageUser');

    /*My Company Profile Management Router*/
    Route::get('/my-company', [UserController::class, 'manageCompany']);
    Route::post('/my-company', [UserController::class, 'manageCompanyPost'])->name('manageCompany');

    Route::get('/reset-password', [UserController::class, 'resetPassword']);
    Route::post('/reset-password', [UserController::class, 'resetPasswordPost'])->name('resetPassword');

    /*User's Role Management Router*/
    Route::get('/role-settings', [SettingController::class, 'roleSettings']);
    Route::get('/manage-role-setting', [SettingController::class, 'manageRoleSettings'])->name('manageRoleSettings');
    Route::post('/manage-role-setting', [SettingController::class, 'manageRoleSettingsPost'])->name('manageRoleSettings');

    Route::resource('email-templates', SettingController::class);
    Route::post('email-templates/{id}/toggle', [SettingController::class, 'toggle'])
        ->name('email-templates.toggle');

    Route::get('/ajax-send', [AjaxController::class, 'ajaxSend']);
    Route::get('/task-search', [AjaxController::class, 'taskSearch'])->name('taskSearch');

    //SMTP Email Setup
    Route::get('/smtp-settings', [SettingController::class, 'smtpSetup'])->name('smtpSetup');
    Route::post('/smtp-settings', [SettingController::class, 'smtpSetupPost'])->name('smtpSetup');

    //Notification Reminders
    Route::get('/reminders', [LeadController::class, 'reminderScript'])->name('reminderScript');
    Route::get('/trigger-url', [AuthController::class, 'triggerCurl']);

    Route::delete('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/signout', function () {
        Auth::logout();

        return redirect()->route('login');
    });
});

Route::get('/test-scheduler', [SchedulerTestController::class, 'run']);

Route::get('/clear-cache', function () {
    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('config:cache');
    $exitCode = Artisan::call('config:clear');
    $exitCode = Artisan::call('view:clear');
    $exitCode = Artisan::call('route:clear');

    // php artisan config:clear
    // php artisan config:cache
    // php artisan view:clear
    // php artisan route:clear

    return 'DONE';
});

Route::get('/debug-fcm', function (Request $request) {
    try {
        $diag = [
            'current_auth_user' => Auth::user() ? ['id' => Auth::user()->id, 'name' => Auth::user()->name] : 'Not Logged In',
            'database_status' => 'Testing...',
            'users_with_tokens' => []
        ];

        try {
            \DB::connection()->getPdo();
            $diag['database_status'] = 'Connected';
            $diag['users_with_tokens'] = \App\Models\User::whereNotNull('fcm_token')->get(['id', 'name', 'fcm_token'])->toArray();
        } catch (\Exception $e) {
            $diag['database_status'] = 'Failed: ' . $e->getMessage();
        }

        return response()->json($diag);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

Route::get('/debug-send-notif', function (Request $request) {
    try {
        $token = $request->query('token');
        $source = 'URL Parameter';

        if (!$token) {
            $user = \App\Models\User::whereNotNull('fcm_token')->latest()->first();
            $token = $user ? $user->fcm_token : null;
            $source = $user ? "Database (User ID: {$user->id}, Name: {$user->name})" : 'None found in DB';
        }

        if (!$token) {
            return response()->json(['status' => 'Error', 'message' => 'No token found', 'source' => $source]);
        }

        \Log::info("Attempting to send FCM to token: " . substr($token, 0, 20) . "... [Source: $source]");

        $factory = (new Factory)->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));
        $messaging = $factory->createMessaging();

        $message = CloudMessage::fromArray([
            'token' => $token,
            'notification' => [
                'title' => 'Esecrm Test',
                'body' => 'Test notification from debug route at ' . now()->toDateTimeString(),
            ],
            'data' => [
                'click_action' => url('/home'),
                'test' => 'true'
            ],
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'channel_id' => 'default_channel'
                ]
            ],
            'webpush' => [
                'headers' => [
                    'Urgency' => 'high'
                ]
            ]
        ]);

        try {
            $report = $messaging->send($message);
            return response()->json([
                'status' => 'Success',
                'token_source' => $source,
                'token_used' => $token,
                'firebase_report' => $report
            ]);
        } catch (\Exception $e) {
            \Log::error("FCM Debug Send Error: " . $e->getMessage());
            return response()->json([
                'status' => 'Error',
                'message' => $e->getMessage(),
                'token_source' => $source,
                'token_used' => $token
            ]);
        }
    } catch (\Exception $e) {
        return response()->json(['status' => 'Fatal Error', 'message' => $e->getMessage()]);
    }
});

Route::get('/test-firebase', function () {
    try {
        $factory = (new Factory)
            ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));

        $messaging = $factory->createMessaging();
        return "Firebase initialized successfully!";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});
