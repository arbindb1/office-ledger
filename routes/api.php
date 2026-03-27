<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ColleagueController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\LedgerQueryController;
use App\Http\Controllers\OrderBatchController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Colleagues
    Route::get('/colleagues', [ColleagueController::class, 'index']);
    Route::post('/colleagues', [ColleagueController::class, 'store']);
    Route::post('/colleagues/{id}/aliases', [ColleagueController::class, 'addAlias']);
    Route::get('/colleagues/{id}/ledger', [ColleagueController::class, 'ledger']);
    Route::post('/colleagues/{colleague}/deactivate', [ColleagueController::class, 'deactivate']);
    Route::get('/colleagues/{id}/analytics', [ColleagueController::class, 'analytics'])->name('colleagues.analytics');

    // Orders
    Route::get('/order-batches', [OrderBatchController::class, 'index']); 
    Route::post('/order-batches', [OrderBatchController::class, 'store']);
    Route::delete('/order-batch/{id}',[OrderBatchController::class,'destroy']);
    Route::post('/order-batches/{id}/items', [OrderBatchController::class, 'addItem']);
    Route::post('/order-batches/{id}/finalize', [OrderBatchController::class, 'finalize']);
    Route::post('/order-batches/{id}/remove-item', [OrderBatchController::class, 'removeBatchItem']);
    Route::get('/order-batches/{id}', [OrderBatchController::class, 'show']);

    // Notifications
    Route::post('/notifications/ingest', [NotificationController::class, 'ingest']);
    Route::get('/notifications/unmatched', [NotificationController::class, 'unmatched']);
    Route::post('/notifications/{id}/assign', [NotificationController::class, 'assign']);
    Route::post('/notifications/{id}/ignore', [NotificationController::class, 'ignore']);
    Route::post('/notifications/{id}/apply', [NotificationController::class, 'apply']);

    // Ledger & Items
    Route::post('/ledger/manual-credit', [LedgerController::class, 'manualCredit']);
    Route::get('/ledger', [LedgerQueryController::class, 'index']);

    Route::get('/items', [ItemsController::class, 'index']);
    Route::post('/items', [ItemsController::class, 'store']);
    Route::patch('/items/{id}', [ItemsController::class, 'update']);
    Route::post('/items/{id}/deactivate', [ItemsController::class, 'deactivate']);
});