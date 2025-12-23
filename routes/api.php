<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GhlController\WebhookController;
use App\Http\Controllers\GhlController\GoHighLevelController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Webhook route - No authentication required for external webhooks
Route::post('/webhook/receive', [WebhookController::class, 'handleWebhook'])->name('webhook.receive');

// Exam completion webhook - Creates record in GHL Custom Object
Route::post('/webhook/exam-completion', [WebhookController::class, 'handleExamCompletion'])->name('webhook.exam-completion');

// TEMPORARILY DISABLED: App Uninstall webhook - Deletes GHL Custom Object and Token
// Route::post('/gohighlevel/uninstall', [GoHighLevelController::class, 'uninstall']);
