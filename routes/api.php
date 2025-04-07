<?php

use App\Http\Controllers\FinanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\HostingProjectController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TechnicalProjectController;
use App\Http\Controllers\UserController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/reports', [ReportController::class, 'index']); // عرض التقارير حسب القسم
    Route::post('/reports', [ReportController::class, 'store']);
    // السماح فقط لـ Super Admin بإنشاء المستخدمين
    Route::middleware('role:super_admin')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::apiResource('/roles', RoleController::class);
        Route::get('/permissions', [RoleController::class, 'getPermissions']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/search', [UserController::class, 'search']);
        Route::get('/role', [UserController::class, 'getRoles']);
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);

        Route::get('/requests/{id}/history', [RequestController::class, 'requestHistory']);
        Route::get('/requests/{id}/history/download', [RequestController::class, 'downloadRequestHistory']);
        Route::get('/projects', [ProjectController::class, 'index']);
        Route::get('/projects/{project}', [ProjectController::class, 'show']);
        Route::get('/developer', [DeveloperController::class, 'index']);
        Route::get('/developer/{developer}', [DeveloperController::class, 'show']);
        Route::get('/marketing', [MarketingController::class, 'index']);
        Route::get('/marketing/{marketing}', [MarketingController::class, 'show']);
        Route::get('/finance', [FinanceController::class, 'index']);
        Route::post('/projects/finance', [FinanceController::class, 'storeProject']);
        Route::post('/expenses', [FinanceController::class, 'storeExpense']);
        Route::get('/departments', [DepartmentController::class, 'index']);
    });
    Route::middleware(['auth:sanctum', 'check_department:Sales'])->group(function () {
        Route::apiResource('projects', ProjectController::class);
        // Route::get('sales/reports', [RequestController::class, 'salesReports']);
        Route::get('/projects/department/{department_id}', [ProjectController::class, 'getProjectsByDepartment']);

        Route::post('/requests', [RequestController::class, 'store']);
    });

    Route::middleware(['auth:sanctum', 'check_department:developer'])->group(function () {
        // Route::apiResource('developer', DeveloperController::class);
        Route::get('developer/create', [DeveloperController::class, 'create'])->name('developer.create'); // عرض نموذج الإضافة
        Route::post('developer', [DeveloperController::class, 'store'])->name('developer.store'); // تخزين مشروع جديد
        Route::get('developer/{developer}/edit', [DeveloperController::class, 'edit'])->name('developer.edit'); // عرض نموذج التعديل
        Route::put('developer/{developer}', [DeveloperController::class, 'update'])->name('developer.update'); // تحديث مشروع
        Route::delete('developer/{developer}', [DeveloperController::class, 'destroy'])->name('developer.destroy'); // حذف مشروع
        Route::get('projects/department/{departmentId}', [DeveloperController::class, 'getProjectsByDepartment']);
        Route::put('/projects/{projectId}/status', [ProjectController::class, 'updateStatus']);

    });
    Route::middleware(['auth:sanctum', 'check_department:Marketing'])->group(function () {
        // Route::apiResource('marketing', MarketingController::class);
        Route::get('marketing/create', [MarketingController::class, 'create'])->name('marketing.create'); // عرض نموذج الإضافة
        Route::post('marketing', [MarketingController::class, 'store'])->name('marketing.store'); // تخزين عنصر جديد
        Route::get('marketing/{marketing}', [MarketingController::class, 'show'])->name('marketing.show'); // عرض عنصر محدد
        Route::get('marketing/{marketing}/edit', [MarketingController::class, 'edit'])->name('marketing.edit'); // عرض نموذج التعديل
        Route::put('marketing/{marketing}', [MarketingController::class, 'update'])->name('marketing.update'); // تحديث عنصر
        Route::delete('marketing/{marketing}', [MarketingController::class, 'destroy'])->name('marketing.destroy'); // حذف عنصر
        Route::get('projects/department/{departmentId}', [MarketingController::class, 'getProjectsByDepartment']);

    });
    Route::apiResource('technical-projects', TechnicalProjectController::class);
    Route::apiResource('hosting-projects', HostingProjectController::class);
    Route::get('projects/{id}/history', [ProjectController::class, 'history']);
    Route::get('developer', [DeveloperController::class, 'index'])->name('developer.index'); // عرض قائمة المشاريع
    Route::get('developer/{developer}', [DeveloperController::class, 'show'])->name('developer.show'); // عرض مشروع محدد
    Route::get('marketing', [MarketingController::class, 'index'])->name('marketing.index'); // عرض قائمة التسويق

    // عرض الطلبات الخاصة بالتيم ليدر
    Route::get('/requests', [RequestController::class, 'index']);
    Route::get('/search', [ProjectController::class, 'search']);
    Route::get('/filter', [ProjectController::class, 'filter']);
    Route::get('/search', [DeveloperController::class, 'search']);
    Route::get('/filter', [DeveloperController::class, 'filter']);
    Route::get('/search', [MarketingController::class, 'search']);
    Route::get('/filterMarket', [MarketingController::class, 'filterMarket']);
    Route::get('/projects', [ProjectController::class, 'index']);

    //  // قبول أو رفض الطلب
    Route::put('/requests/{teamLeadRequest}', [RequestController::class, 'update']);
    Route::delete('/requests/{teamLeadRequest}', [RequestController::class, 'destroy']);

    Route::get('/team-by-department', [UserController::class, 'getTeamByDepartment']);
    Route::get('/team-leaders', [UserController::class, 'getTeamLeaders']);
    Route::get('/projects/{id}/download', [ProjectController::class, 'generateProjectPDF']);
    Route::get('/requests/{id}/history/pdf', [RequestController::class, 'downloadHistoryPdf']);
});
