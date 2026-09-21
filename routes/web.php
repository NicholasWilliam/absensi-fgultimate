<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckinController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Karyawan
Route::middleware(['auth', 'role:karyawan'])->group(function () {
    Route::get('/checkin', [CheckinController::class, 'index'])->name('checkin');
    Route::post('/checkin', [CheckinController::class, 'store'])->name('checkin.store');
});

// Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/report', [ReportController::class, 'index'])->name('report');
    Route::get('/report/export', [ReportController::class, 'export'])->name('report.export');

    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');
    Route::post('/schedule', [ScheduleController::class, 'store'])->name('schedule.store');
    Route::delete('/schedule/{shift}', [ScheduleController::class, 'destroy'])->name('schedule.destroy');

    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::patch('/employees/{user}/toggle', [EmployeeController::class, 'toggle'])->name('employees.toggle');

    Route::get('/settings', [SettingController::class, 'edit'])->name('settings');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
});
