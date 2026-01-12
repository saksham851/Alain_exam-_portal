<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\CaseStudyController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ComprehensiveExportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes (Breeze)
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    
    // Profile routes (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dashboard routing based on role
    Route::get('/dashboard', function () {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('student.dashboard');
    })->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // --- Dashboard ---
    Route::get('dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // --- Users ---
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    
    // --- Exams (Controller) ---
    Route::prefix('exams')->name('exams.')->group(function () {
        Route::get('/', [ExamController::class,'index'])->name('index');
        Route::get('create', [ExamController::class,'create'])->name('create');
        Route::post('/', [ExamController::class,'store'])->name('store');
        Route::get('{id}/edit', [ExamController::class,'edit'])->name('edit');
        Route::put('{id}', [ExamController::class,'update'])->name('update');
        Route::delete('{id}', [ExamController::class,'destroy'])->name('destroy');
        Route::get('export/csv', [ExamController::class,'export'])->name('export');
        Route::post('import/csv', [ExamController::class,'import'])->name('import');
        Route::put('{id}/toggle-status', [ExamController::class, 'toggleStatus'])->name('toggle-status');
    });

    // --- Exam Categories ---
    Route::resource('exam-categories', \App\Http\Controllers\Admin\ExamCategoryController::class);

    // --- Questions (Controller) ---
    Route::resource('questions', QuestionController::class);
    Route::get('questions-ajax/case-studies/{examId}', [QuestionController::class, 'getCaseStudies'])->name('questions.getCaseStudies');
    Route::get('questions-ajax/sub-case-studies/{caseStudyId}', [QuestionController::class, 'getSubCaseStudies'])->name('questions.getSubCaseStudies');
    Route::get('questions/export/csv', [QuestionController::class, 'export'])->name('questions.export');
    Route::post('questions/import/csv', [QuestionController::class, 'import'])->name('questions.import');

    // --- Sections (formerly Case Studies) ---
    // We keep the route name 'case-studies' for now to avoid breaking views, 
    // but the controller is SectionController (handling parent blocks).
    // Ideally we should rename route to 'sections' but views heavily rely on route('admin.case-studies.*').
    // So we map: Route 'case-studies' -> SectionController.
    Route::resource('case-studies', SectionController::class);
    Route::get('case-studies/export/csv', [SectionController::class, 'export'])->name('case-studies.export');
    Route::post('case-studies/import/csv', [SectionController::class, 'import'])->name('case-studies.import');

    // --- Case Studies Bank ---
    Route::prefix('case-studies-bank')->name('case-studies-bank.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CaseStudyBankController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\CaseStudyBankController::class, 'create'])->name('create');
        Route::post('/store', [\App\Http\Controllers\Admin\CaseStudyBankController::class, 'store'])->name('store');
        Route::post('/copy', [\App\Http\Controllers\Admin\CaseStudyBankController::class, 'copy'])->name('copy');
        Route::get('/sections/{examId}', [\App\Http\Controllers\Admin\CaseStudyBankController::class, 'getSectionsByExam'])->name('sections');
    });

    // --- Case Studies (formerly Sub Case Studies) ---
    // Similarly, we keep route name 'sub-case-studies' to minimize view breakage.
    // Route 'sub-case-studies' -> CaseStudyController.
    Route::resource('sub-case-studies', CaseStudyController::class);

    // --- Attempts ---
    Route::prefix('attempts')->name('attempts.')->group(function() {
        Route::get('/', [\App\Http\Controllers\Admin\AttemptController::class, 'index'])->name('index');
        Route::get('{id}', [\App\Http\Controllers\Admin\AttemptController::class, 'show'])->name('show');
        Route::get('user/{userId}', [\App\Http\Controllers\Admin\AttemptController::class, 'byUser'])->name('by-user');
    });

    // --- Comprehensive Export/Import (Complete Hierarchy) ---
    Route::prefix('data')->name('data.')->group(function() {
        Route::get('/', function() { return view('admin.data.index'); })->name('index');
        Route::get('export-complete', [ComprehensiveExportController::class, 'exportComplete'])->name('export-complete');
        Route::post('import-complete', [ComprehensiveExportController::class, 'importComplete'])->name('import-complete');
        Route::get('download-sample', [ComprehensiveExportController::class, 'downloadSample'])->name('download-sample');
    });

});


/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'student'])->prefix('student')->name('student.')->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\Student\DashboardController::class, 'index'])->name('dashboard');
    Route::get('history', [\App\Http\Controllers\Student\DashboardController::class, 'history'])->name('history');
});

Route::middleware(['auth', 'student'])->prefix('exams')->name('exams.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Student\ExamController::class, 'index'])->name('index');
    Route::get('/{id}', [\App\Http\Controllers\Student\ExamController::class, 'show'])->name('show');
    Route::post('/{id}/start', [\App\Http\Controllers\Student\ExamController::class, 'start'])->name('start');
    Route::get('/{id}/take', [\App\Http\Controllers\Student\ExamController::class, 'take'])->name('take');
    Route::post('/{id}/submit', [\App\Http\Controllers\Student\ExamController::class, 'submit'])->name('submit');
    Route::get('/result/{attemptId}', [\App\Http\Controllers\Student\ExamController::class, 'result'])->name('result');
    Route::get('/download/{attemptId}', [\App\Http\Controllers\Student\ExamController::class, 'download'])->name('download');
    Route::get('/{id}/answer-key', [\App\Http\Controllers\Student\ExamController::class, 'downloadAnswerKey'])->name('answer-key');
});




Route::get('/gohighlevel/initiate', [App\Http\Controllers\GhlController\GoHighLevelController::class, 'initiate']);
Route::get('/getAccessToken', [App\Http\Controllers\GhlController\GoHighLevelController::class, 'callback']);
