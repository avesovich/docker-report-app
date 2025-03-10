<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\RestrictExecutives;
use App\Http\Controllers\Article\ArticleController;
use App\Http\Controllers\NewsController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return Inertia::render('Auth/Login');
});


Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'throttle:60,1'])->name('dashboard');


Route::feeds();

Route::middleware(['auth', 'verified', 'role:administrator|editor|executive', 'throttle:60,1'])->group(function () {
    Route::get('/status/{status}', [ArticleController::class, 'index'])
        ->middleware([RestrictExecutives::class, 'can:view article report'])
        ->where('status', 'Review|Updated|Revision|Evaluated|Approved')
        ->name('status.index');
    Route::get('/status/{status}/view/{id}', [ArticleController::class, 'show'])
        ->middleware([RestrictExecutives::class, 'can:view article report'])
        ->where('status', 'Review|Updated|Revision|Evaluated|Approved')
        ->name('status.view');
    Route::get('/articles/{id}/comments', [CommentController::class, 'index']);
    Route::get('/api/reports', [ChartController::class, 'getReports']);
    Route::get('/api/line-data', [ChartController::class, 'getLineData']);
    Route::get('/api/stats', [ChartController::class, 'showStats']);
    Route::get('/export/articles/{status}', [ArticleController::class, 'exportCsv'])->name('articles.export');
});

Route::middleware(['auth', 'verified', 'role:administrator|executive', 'throttle:60,1'])->group(function () {
    Route::post('/articles/{id}/approve', [ArticleController::class, 'setApprovalStatus'])
        ->name('articles.approve');
    Route::post('/articles/{id}/comments', [CommentController::class, 'store']);
});

Route::middleware(['auth', 'verified', 'role:editor', 'throttle:60,1'])->group(function () {
    Route::get('/create/report', function () {
        return Inertia::render('Forms/CreateReport');
    })->middleware('can:create article report')->name('form.create');
    Route::post('/create/report', [ArticleController::class, 'store'])
        ->middleware('can:create article report')
        ->name('create.report');
    Route::put('/articles/{id}/update', [ArticleController::class, 'update'])
        ->name('status.update');
    Route::get('/status/update/{id}', [ArticleController::class, 'edit'])
        ->name('view.update');
});

Route::middleware(['auth', 'verified', 'role:administrator', 'throttle:60,1'])
    ->prefix('admin/users')
    ->name('users.')
    ->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth', 'role:editor'])->group(function () {
    Route::post('/api/upload-images', [ArticleController::class, 'uploadImages']);

    Route::get('/api/upload-images', function () {
        return response()->json([
            'success' => false,
            'message' => 'Method Not Allowed. Use POST instead.'
        ], 405);
    });
});


Route::middleware(['auth', 'role:administrator|editor|executive'])->group(function () {
    Route::get('/image/article/{filename}', [ArticleController::class, 'getImage']);
});

Route::middleware('auth', 'throttle:60,1')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/news', [NewsController::class, 'index'])->name('news.index');
});

require __DIR__.'/auth.php';
