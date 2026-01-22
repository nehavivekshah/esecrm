<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Kreait\Firebase\Factory;
use App\Http\Controllers\FCMController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LeadApiController;
use App\Http\Controllers\ClientApiController;
use App\Http\Controllers\InvoiceApiController;
use App\Http\Controllers\TaskApiController;
use App\Http\Controllers\TeamApiController;
use App\Http\Controllers\EmailApiController;
use App\Http\Controllers\ReportingApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ====================================================================
// PUBLIC ROUTES (No Authentication Required)
// ====================================================================

Route::post('/v1/auth/register', [AuthController::class, 'register']);
Route::post('/v1/auth/login', [AuthController::class, 'login']);
Route::get('/v1/check-login', [ApiController::class, 'checkLogin']);

// ====================================================================
// PROTECTED ROUTES (Authentication Required)
// ====================================================================

Route::middleware('auth:sanctum')->group(function () {
    
    // ===== AUTHENTICATION ROUTES =====
    Route::prefix('/v1/auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
    });

    // ===== LEAD MANAGEMENT ROUTES =====
    Route::prefix('/v1/leads')->group(function () {
        Route::get('/', [LeadApiController::class, 'index']);
        Route::post('/', [LeadApiController::class, 'store']);
        Route::get('/statistics', [LeadApiController::class, 'statistics']);
        Route::get('/{id}', [LeadApiController::class, 'show']);
        Route::put('/{id}', [LeadApiController::class, 'update']);
        Route::delete('/{id}', [LeadApiController::class, 'destroy']);
        Route::post('/{id}/assign', [LeadApiController::class, 'assign']);
        Route::post('/{id}/comments', [LeadApiController::class, 'addComment']);
        Route::get('/{id}/comments', [LeadApiController::class, 'getComments']);
    });

    // ===== CLIENT MANAGEMENT ROUTES =====
    Route::prefix('/v1/clients')->group(function () {
        Route::get('/', [ClientApiController::class, 'index']);
        Route::post('/', [ClientApiController::class, 'store']);
        Route::get('/statistics', [ClientApiController::class, 'statistics']);
        Route::get('/{id}', [ClientApiController::class, 'show']);
        Route::get('/{id}/history', [ClientApiController::class, 'history']);
        Route::put('/{id}', [ClientApiController::class, 'update']);
        Route::delete('/{id}', [ClientApiController::class, 'destroy']);
    });

    // ===== INVOICE MANAGEMENT ROUTES =====
    Route::prefix('/v1/invoices')->group(function () {
        Route::get('/', [InvoiceApiController::class, 'index']);
        Route::post('/', [InvoiceApiController::class, 'store']);
        Route::get('/statistics', [InvoiceApiController::class, 'statistics']);
        Route::get('/{id}', [InvoiceApiController::class, 'show']);
        Route::get('/{id}/pdf', [InvoiceApiController::class, 'generatePdf']);
        Route::put('/{id}', [InvoiceApiController::class, 'update']);
        Route::delete('/{id}', [InvoiceApiController::class, 'destroy']);
        Route::post('/{id}/mark-sent', [InvoiceApiController::class, 'markSent']);
        Route::post('/{id}/mark-paid', [InvoiceApiController::class, 'markPaid']);
    });

    // ===== TASK MANAGEMENT ROUTES =====
    Route::prefix('/v1/tasks')->group(function () {
        Route::get('/', [TaskApiController::class, 'index']);
        Route::post('/', [TaskApiController::class, 'store']);
        Route::get('/statistics', [TaskApiController::class, 'statistics']);
        Route::get('/{id}', [TaskApiController::class, 'show']);
        Route::put('/{id}', [TaskApiController::class, 'update']);
        Route::delete('/{id}', [TaskApiController::class, 'destroy']);
        Route::post('/{id}/comments', [TaskApiController::class, 'addComment']);
        Route::post('/{id}/log-hours', [TaskApiController::class, 'logHours']);
    });

    // ===== TEAM MANAGEMENT ROUTES =====
    Route::prefix('/v1/team')->group(function () {
        Route::get('/', [TeamApiController::class, 'index']);
        Route::get('/performance', [TeamApiController::class, 'performance']);
        Route::get('/workload', [TeamApiController::class, 'workload']);
        Route::get('/{id}', [TeamApiController::class, 'show']);
        Route::put('/{id}', [TeamApiController::class, 'update']);
    });

    // ===== ATTENDANCE ROUTES =====
    Route::prefix('/v1/attendance')->group(function () {
        Route::get('/', [TeamApiController::class, 'attendance']);
        Route::post('/check-in', [TeamApiController::class, 'checkIn']);
        Route::post('/check-out', [TeamApiController::class, 'checkOut']);
        Route::post('/{user_id}/mark', [TeamApiController::class, 'markAttendance']);
        Route::get('/summary', [TeamApiController::class, 'attendanceSummary']);
    });

    // ===== EMAIL MANAGEMENT ROUTES =====
    Route::prefix('/v1/emails')->group(function () {
        // Templates
        Route::get('/templates', [EmailApiController::class, 'index']);
        Route::post('/templates', [EmailApiController::class, 'store']);
        Route::get('/templates/{id}', [EmailApiController::class, 'show']);
        Route::put('/templates/{id}', [EmailApiController::class, 'update']);
        Route::delete('/templates/{id}', [EmailApiController::class, 'destroy']);
        Route::post('/templates/{id}/render', [EmailApiController::class, 'render']);
        Route::post('/templates/{id}/test', [EmailApiController::class, 'test']);

        // Email operations
        Route::post('/send', [EmailApiController::class, 'send']);
        Route::post('/schedule', [EmailApiController::class, 'schedule']);
        Route::get('/scheduled', [EmailApiController::class, 'scheduled']);
        Route::get('/statistics', [EmailApiController::class, 'statistics']);
    });

    // ===== REPORTING & ANALYTICS ROUTES =====
    Route::prefix('/v1/reports')->group(function () {
        Route::get('/dashboard', [ReportingApiController::class, 'dashboard']);
        Route::get('/sales-pipeline', [ReportingApiController::class, 'salesPipeline']);
        Route::get('/revenue', [ReportingApiController::class, 'revenue']);
        Route::get('/top-clients', [ReportingApiController::class, 'topClients']);
        Route::get('/team-performance', [ReportingApiController::class, 'teamPerformance']);
        Route::get('/forecast', [ReportingApiController::class, 'forecast']);
        Route::get('/activity-log', [ReportingApiController::class, 'activityLog']);
        Route::get('/custom', [ReportingApiController::class, 'custom']);
    });

    // ===== FCM & NOTIFICATIONS (EXISTING) =====
    Route::prefix('/v1')->group(function () {
        Route::get('/registerfcm', [ApiController::class, 'registerFcm']);
        Route::get('/send-notification', [ApiController::class, 'sendNotification']);
        Route::post('/enquiry', [ApiController::class, 'enquiryPost']);
    });
});

// ====================================================================
// MISCELLANEOUS
// ====================================================================

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});