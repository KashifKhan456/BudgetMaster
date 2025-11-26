<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\RecurringTransactionController;
use App\Http\Controllers\SavingsGoalController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExpenseRequestController;
use App\Http\Controllers\ReportController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('categories', CategoryController::class);
    Route::resource('budgets', BudgetController::class);
    Route::post('/budgets/{budget}/share', [BudgetController::class, 'share'])->name('budgets.share');
    Route::delete('/budgets/{budget}/share/{user}', [BudgetController::class, 'unshare'])->name('budgets.unshare');
    Route::resource('expense-requests', ExpenseRequestController::class)->only(['index', 'store', 'update']); // Added this line
    Route::resource('transactions', TransactionController::class);
    Route::resource('recurring', RecurringTransactionController::class);
    Route::resource('goals', SavingsGoalController::class);
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/export', [ExportController::class, 'export'])->name('export.transactions');
    Route::post('/import', [ImportController::class, 'store'])->name('import.transactions');

    Route::group(['middleware' => ['can:manage users']], function () {
        Route::resource('users', UserController::class);
    });

    Route::put('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::put('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
