<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Import Controllers
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Api\ApiTransactionController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LonjaranController;
use App\Http\Controllers\TransactionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ========================================================================
// ROUTE PUBLIK (Tanpa Login)
// ========================================================================
Route::post('/login', [AuthController::class, 'login']);

// ========================================================================
// ROUTE PRIVAT (Memerlukan Token)
// ========================================================================
Route::middleware('auth:sanctum')->group(function () {
    
    // ===== AUTH =====
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // ===== PRODUCTS =====
    Route::apiResource('products', ProductController::class);
    
    // ===== TRANSACTION ROUTES =====
    Route::post('/checkout', [ApiTransactionController::class, 'checkout']);
    Route::get('/history', [ApiTransactionController::class, 'history']);
    Route::post('/orders/{id}/mark-lunas', [ApiTransactionController::class, 'markLunas']); // ← ROUTE UNTUK LUNASI DP

    // ===== DASHBOARD =====
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // ===== PROJECTS =====
    Route::apiResource('projects', ProjectController::class);
    Route::get('/projects-stats/dashboard', [ProjectController::class, 'dashboardStats']);

    // ===== ATTENDANCE =====
    Route::post('/attendances', [AttendanceController::class, 'store']);
    Route::get('/attendances', [AttendanceController::class, 'index']);
    Route::post('/employees', [AttendanceController::class, 'storeEmployee']);
    Route::get('/employees', [AttendanceController::class, 'getEmployees']);
    Route::delete('/employees/{id}', [AttendanceController::class, 'deleteEmployee']);

    // ===== LONJARAN =====
    Route::get('/lonjaran', [LonjaranController::class, 'index']);
    Route::get('/lonjaran/history', [LonjaranController::class, 'history']);
    Route::get('/lonjaran/{id}', [LonjaranController::class, 'show']);
    Route::post('/lonjaran', [LonjaranController::class, 'store']);
    Route::put('/lonjaran/{id}', [LonjaranController::class, 'update']);
    Route::delete('/lonjaran/{id}', [LonjaranController::class, 'destroy']);

    // ===== TRANSACTIONS (NEW - POS SYSTEM) - OPSIONAL =====
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::post('/create', [TransactionController::class, 'store']);
        Route::get('/{id}', [TransactionController::class, 'show']);
        Route::post('/{id}/upload-proof', [TransactionController::class, 'uploadProof']);
        Route::get('/{id}/proof', [TransactionController::class, 'getProof']);
        Route::delete('/{id}', [TransactionController::class, 'destroy']);
    });
    
    // ===== TEST TOKEN (DEBUG) =====
    Route::get('/test-token', function (Request $request) {
        return response()->json([
            'message' => 'Token valid',
            'user' => $request->user()
        ]);
    });
    
}); // END MIDDLEWARE GROUP