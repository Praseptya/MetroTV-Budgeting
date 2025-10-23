<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HelpCenterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MasterItemController;
use App\Http\Controllers\MasterProgramController;
use App\Http\Controllers\MasterTemplateController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to login
Route::get('/', fn () => redirect('/login'));

// Authentication
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'getBudgetStats'])->name('dashboard.stats');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
    Route::get('/dashboard/report', [DashboardController::class, 'report'])->name('dashboard.report');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
    Route::get('/dashboard/table-data', [DashboardController::class, 'tableData'])->name('dashboard.table-data');

    // Budgets (show/edit/destroy saja)
    Route::middleware('auth')->group(function () {
        Route::get('/budgets/create', [BudgetController::class, 'create'])->name('budgets.create');
        Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');
        Route::get('/budgets/{id}', [BudgetController::class, 'show'])->name('budgets.show');
        Route::get('/budgets/{id}/edit', [BudgetController::class, 'edit'])->name('budgets.edit');
        Route::delete('/budgets/{id}', [BudgetController::class, 'destroy'])->name('budgets.destroy');
    });

    // ===== Users =====
    Route::middleware(['auth', 'can:admin-only'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{id}/reassign-delete', [UserController::class, 'reassignAndDestroy'])->name('users.reassign_delete');
        Route::get('/users/reset-password', [UserController::class, 'showSelfResetForm'])->name('users.reset.form');
        Route::post('/users/reset-password', [UserController::class, 'sendSelfResetOtp'])->name('users.reset.send');
        Route::post('/users/reset-password/verify', [UserController::class, 'verifySelfResetOtp'])->name('users.reset.verify');
    });

    // ===== Master Data (editable only) =====
    Route::middleware('can:master-editable')->group(function () {
        Route::post('/master/items', [MasterItemController::class, 'store'])->name('master.items.store');
        Route::put('/master/items/{id}', [MasterItemController::class, 'update'])->name('master.items.update');
        Route::delete('/master/items/{id}', [MasterItemController::class, 'destroy'])->name('master.items.destroy');

        Route::post('/master/program', [MasterProgramController::class, 'store'])->name('program.store');
        Route::put('/master/program/{id}', [MasterProgramController::class, 'update'])->name('program.update');
        Route::delete('/master/program/{id}', [MasterProgramController::class, 'destroy'])->name('program.destroy');

        Route::post('/master/templates', [MasterTemplateController::class, 'store'])->name('store');
        Route::put('/master/templates/{id}', [MasterTemplateController::class, 'update'])->name('update');
        Route::delete('/master/templates/{id}', [MasterTemplateController::class, 'destroy'])->name('destroy');
    });

    // ===== Master Data =====
    Route::prefix('master')->name('master.')->group(function () {
        // Master Items
        Route::get('/items', [MasterItemController::class, 'index'])->name('items.index');
        Route::post('/items', [MasterItemController::class, 'store'])->name('items.store');
        Route::put('/items/{id}', [MasterItemController::class, 'update'])->name('items.update'); // <-- penting
        Route::delete('/items/{id}', [MasterItemController::class, 'destroy'])->name('items.destroy');

        // Master Program
        Route::get('/program', [MasterProgramController::class, 'index'])->name('program.index');
        Route::post('/program', [MasterProgramController::class, 'store'])->name('program.store');
        Route::get('/program/{id}/edit', [MasterProgramController::class, 'edit'])->name('master.program.edit');
        Route::put('/program/{id}', [MasterProgramController::class, 'update'])->name('program.update'); // <-- penting
        Route::delete('/program/{id}', [MasterProgramController::class, 'destroy'])->name('program.destroy');
    });

    // ===== Master Template =====
    Route::prefix('master/templates')->name('master.templates.')->group(function () {
        // --- AJAX / JSON (letakkan DI ATAS route {id}) ---
        Route::get('item-search', [MasterTemplateController::class, 'itemSearch'])
            ->name('item.search');

        Route::get('programs/{id}/detail', [MasterTemplateController::class, 'programDetail'])
            ->whereNumber('id')
            ->name('program.detail');

        // --- Halaman utama ---
        Route::get('/', [MasterTemplateController::class, 'index'])->name('index');
        Route::post('/', [MasterTemplateController::class, 'store'])->name('store');

        // --- CRUD by id (dibatasi numeric supaya tidak nabrak 'item-search') ---
        Route::get('{id}/edit', [MasterTemplateController::class, 'edit'])
            ->whereNumber('id')
            ->name('edit');

        Route::get('{id}', [MasterTemplateController::class, 'show'])
            ->whereNumber('id')
            ->name('show');

        Route::delete('{id}', [MasterTemplateController::class, 'destroy'])
            ->whereNumber('id')
            ->name('destroy');

        // --- Items di dalam Template ---
        Route::post('{id}/items', [MasterTemplateController::class, 'storeItem'])
            ->whereNumber('id')
            ->name('items.store');

        Route::delete('{id}/items/{rowId}', [MasterTemplateController::class, 'destroyItem'])
            ->whereNumber('id')
            ->whereNumber('rowId')
            ->name('items.destroy');

        Route::patch('{id}/items/{rowId}/qty', [MasterTemplateController::class, 'updateItemQty'])
            ->name('items.qty');
    });

    // ===== Budgets =====
    Route::middleware('auth')->group(function () {

        Route::prefix('budgets')->name('budgets.')->group(function () {

            // ========== AJAX (detail template) ==========
            Route::get('templates/{id}/detail', [BudgetController::class, 'templateDetail'])
                ->whereNumber('id')
                ->name('template.detail');

            // ========== CREATE & STORE ==========
            Route::get('create', [BudgetController::class, 'create'])->name('create');
            Route::post('/', [BudgetController::class, 'store'])->name('store');

            // ========== SHOW (detail budget) ==========
            Route::get('{id}', [BudgetController::class, 'show'])
                ->whereNumber('id')
                ->name('show');

            // ========== EDIT & UPDATE ==========
            Route::get('{id}/edit', [BudgetController::class, 'edit'])
                ->whereNumber('id')
                ->name('edit');
            Route::put('{id}', [BudgetController::class, 'update'])
                ->whereNumber('id')
                ->name('update');

            // ========== DELETE ==========
            Route::delete('{id}', [BudgetController::class, 'destroy'])
                ->whereNumber('id')
                ->name('destroy');

            // Item actions (Edit Budget)
            Route::post('{id}/items', [BudgetController::class, 'storeItem'])->name('items.store');
            Route::delete('{id}/items/{rowId}', [BudgetController::class, 'destroyItem'])->name('items.destroy');
            Route::patch('{id}/items/{rowId}/qty', [BudgetController::class, 'updateItemQty'])->name('items.qty');

            // AJAX search master items untuk modal Add Item
            Route::get('item-search', [BudgetController::class, 'itemSearch'])->name('item.search');
        });
    });

    // ===== Approval Budget =====
    Route::middleware(['auth', 'check.role:admin,manager,director'])
        ->prefix('approval')
        ->name('approval.')
        ->group(function () {
            Route::get('/', [ApprovalController::class, 'index'])->name('index');
            Route::post('/approve/{id}', [ApprovalController::class, 'approve'])->name('approve');
            Route::post('/reject/{id}', [ApprovalController::class, 'reject'])->name('reject');
        });

    Route::middleware('auth')->group(function () {
        Route::get('/help', [HelpCenterController::class, 'index'])->name('help.index');

        Route::prefix('account')->name('account.')->group(function () {
            Route::get('/password', [AccountController::class, 'passwordForm'])->name('password');
            Route::post('/password/send', [AccountController::class, 'sendOtp'])->name('password.send');
            Route::post('/password/verify', [AccountController::class, 'verifyAndUpdate'])->name('password.verify');
        });
    });

});
