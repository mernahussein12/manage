<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use Spatie\Permission\Models\Permission;

class DepartmentPermissionSeeder extends Seeder
{
    public function run()
    {
        // تحديد الصلاحيات لكل قسم
        $departmentsPermissions = [
            'Marketing' => [
                'Add_project_marketing', 'All_request_marketing', 'Status_request_marketing', 'All_marketing', 'show_marketing', 'delete_marketing'
            ],
            'Sales' => [
                'Add_project_sales', 'All_request_sales', 'Add_request_sales', 'All_sales', 'show_sales', 'delete_sales'
            ],
            'Technical Support' => [
                'Add_project_technical', 'All_technical', 'show_technical', 'edit_technical', 'delete_technical'
            ],
            'Hosting' => [
                'Add_project_hosting', 'All_hosting', 'show_hosting', 'edit_hosting', 'delete_hosting'
            ],
            'HR' => [
                'add_report', 'all_report'
            ],
            'developer' => [
                'Add_project_developer', 'All_request_developer', 'Status_request_developer', 'All_developer', 'show_developer', 'delete_developer'
            ]
        ];

        // إضافة الأقسام وتخصيص الصلاحيات لكل قسم
        foreach ($departmentsPermissions as $departmentName => $permissions) {
            // الحصول على القسم بناءً على الاسم
            $department = Department::where('name', $departmentName)->first();

            // إذا كان القسم موجودًا، ربط الصلاحيات به
            if ($department) {
                // العثور على الصلاحيات بناءً على الأسماء
                $permissionIds = Permission::whereIn('name', $permissions)->pluck('id')->toArray();

                // ربط الأقسام بالصلاحيات عبر الجدول الوسيط department_permission
                $department->permissions()->attach($permissionIds);
            }
        }
    }
}

