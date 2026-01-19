<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\DataManagementController;
use App\Http\Controllers\Admin\UserAnswerController; // If used
use App\Http\Controllers\Admin\ExamCategoryController;
use App\Http\Controllers\Admin\ExamController; // Ensure this is imported
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\CaseStudyController;
use App\Http\Controllers\Admin\SectionController;

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

// Auth Routes (Guest only)
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/', [AuthenticatedSessionController::class, 'store'])->name('login.post');
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');

    // Admin Routes
    Route::name('admin.')->prefix('admin')->group(function () {

        // Users Management
        Route::resource('users', UserController::class);
        Route::patch('users/{id}/activate', [UserController::class, 'activate'])->name('users.activate');
        
        // User Attempts Management
        Route::get('users/{id}/assigned-exams', [UserController::class, 'getAssignedExams'])->name('users.assigned-exams');
        Route::get('users/{studentId}/exam/{examId}/attempts', [UserController::class, 'getStudentExamAttempts'])->name('users.exam-attempts');
        Route::post('users/manage-attempts', [UserController::class, 'manageAttempts'])->name('users.manage-attempts');

        // Role & Permission Management
        Route::resource('roles-permissions', RolePermissionController::class);

        // Data Management (Import)
        Route::get('data-management', [DataManagementController::class, 'index'])->name('data.index');
        Route::post('data-management/import', [DataManagementController::class, 'import'])->name('data.import');


        // Exam Categories
        Route::resource('exam-categories', ExamCategoryController::class);
        Route::patch('exam-categories/{id}/activate', [ExamCategoryController::class, 'activate'])->name('exam-categories.activate');


        // Exams Management
        Route::resource('exams', ExamController::class);
        Route::patch('exams/{id}/activate', [ExamController::class, 'activate'])->name('exams.activate');
        Route::post('exams/{id}/publish', [ExamController::class, 'publish'])->name('exams.publish'); // New Publish Route
        Route::put('exams/{id}/toggle-status', [ExamController::class, 'toggleStatus'])->name('exams.toggle-status');
        Route::post('exams/{id}/clone', [ExamController::class, 'clone'])->name('exams.clone');

        // Questions Management
        Route::resource('questions', QuestionController::class);
        Route::match(['get', 'patch'], 'questions/{id}/activate', [QuestionController::class, 'activate'])->name('questions.activate');
        Route::post('questions/import', [QuestionController::class, 'import'])->name('questions.import');
        Route::post('questions/clone', [QuestionController::class, 'clone'])->name('questions.clone');
        Route::get('questions/export', [QuestionController::class, 'export'])->name('questions.export');

        // Case Studies Bank
        Route::prefix('case-studies-bank')->name('case-studies-bank.')->group(function() {
            Route::get('/', [\App\Http\Controllers\Admin\CaseStudyBankController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\CaseStudyBankController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\CaseStudyBankController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\Admin\CaseStudyBankController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\Admin\CaseStudyBankController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\Admin\CaseStudyBankController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\CaseStudyBankController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/activate', [\App\Http\Controllers\Admin\CaseStudyBankController::class, 'activate'])->name('activate');
            Route::post('/copy', [\App\Http\Controllers\Admin\CaseStudyBankController::class, 'copy'])->name('copy');
        });


         // AJAX Routes for dynamic dropdowns
         Route::get('questions-ajax/case-studies/{examId}', [SectionController::class, 'getSections']);
         Route::get('questions-ajax/sub-case-studies/{sectionId}', [\App\Http\Controllers\Admin\CaseStudyBankController::class, 'getCaseStudiesBySection']);
         Route::get('questions-ajax/questions/{caseStudyId}', [\App\Http\Controllers\Admin\QuestionController::class, 'getQuestionsByCaseStudy']);

         // Sections
         Route::resource('sections', SectionController::class);
         Route::patch('sections/{id}/activate', [SectionController::class, 'activate'])->name('sections.activate');
         Route::post('sections/{section}/clone', [SectionController::class, 'clone'])->name('sections.clone');
         Route::post('sections/clone-external', [SectionController::class, 'clone'])->name('sections.clone-external');

         // Results & Attempts
        Route::get('attempts', [\App\Http\Controllers\Admin\AttemptController::class, 'index'])->name('attempts.index');
        Route::get('attempts/{attempt_id}', [\App\Http\Controllers\Admin\AttemptController::class, 'show'])->name('attempts.show');
        Route::get('attempts/by-user/{userId}', [\App\Http\Controllers\Admin\AttemptController::class, 'byUser'])->name('attempts.by-user');

    });

    // Session Keep Alive
    Route::post('/keep-alive', function () {
        return response()->json(['status' => 'ok']);
    })->name('keep-alive');
});
