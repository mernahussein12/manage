<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    // إنشاء دور جديد مع الصلاحيات
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|unique:roles,name',
    //         'display_name_ar' => 'nullable|string|max:255',
    //         'display_name_en' => 'nullable|string|max:255',
    //         'permissions' => 'required|array', // يجب أن يكون مصفوفة من IDs
    //         'permissions.*' => 'exists:permissions,id',
    //     ]);

    //     $role = Role::create([
    //         'name' => $request->name,
    //         'display_name_ar' => $request->display_name_ar,
    //         'display_name_en' => $request->display_name_en,
    //         // 'guard_name' => 'sanctum', // ✅ تأكد من استخدام `sanctum`
    //         'guard_name' => 'web',
    //     ]);

    //     // إسناد الصلاحيات للدور الجديد
    //     $permissions = Permission::whereIn('id', $request->permissions)->get();
    //     $role->syncPermissions($permissions);

    //     return response()->json([
    //         'message' => 'Role created successfully',
    //         'role' => $role
    //     ], 201);
    // }

    public function store(Request $request)
    {
        // التحقق من البيانات المدخلة
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'display_name_ar' => 'nullable|string|max:255',
            'display_name_en' => 'nullable|string|max:255',
            'permissions' => 'required|array', // يجب أن يكون مصفوفة من IDs
            'permissions.*' => 'exists:permissions,id',
            'department_id' => 'required|exists:departments,id', // التحقق من القسم
        ]);

        // طباعة البيانات المدخلة لمراجعتها
        Log::info('Request Data', $request->all());

        // إنشاء الدور الجديد
        $role = Role::create([
            'name' => $request->name,
            'display_name_ar' => $request->display_name_ar,
            'display_name_en' => $request->display_name_en,
            'guard_name' => 'web', // يمكن تعديلها بحسب الحاجة
        ]);

        // فلترة الصلاحيات بناءً على الـ department_id
        $permissions = Permission::whereIn('id', $request->permissions)
            ->whereHas('departments', function ($query) use ($request) {
                $query->where('departments.id', $request->department_id);
            })
            ->get();

        // طباعة الصلاحيات بعد الفلترة
        Log::info('Filtered Permissions', $permissions->toArray());

        // إذا لم توجد صلاحيات بعد الفلترة، إرجاع رسالة خطأ
        if ($permissions->isEmpty()) {
            return response()->json([
                'message' => 'No permissions found for this department'
            ], 400);
        }

        // إسناد الصلاحيات للدور الجديد
        $role->syncPermissions($permissions);

        // طباعة الصلاحيات المرتبطة بالدور بعد الإسناد
        Log::info('Role Permissions', $role->permissions->toArray());

        // إرجاع الاستجابة
        return response()->json([
            'message' => 'Role created successfully',
            'role' => $role
        ], 201);
    }
    // عرض جميع الأدوار مع الصلاحيات
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return response()->json($roles);
    }

    // جلب جميع الصلاحيات للاستخدام في Select
    public function getPermissions()
    {
        $permissions = Permission::all();
        return response()->json($permissions);
    }

    // تعديل الدور مع تحديث الصلاحيات
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'display_name_ar' => 'nullable|string|max:255',
            'display_name_en' => 'nullable|string|max:255',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $request->name,
            'display_name_ar' => $request->display_name_ar,
            'display_name_en' => $request->display_name_en,
        ]);

        // تحديث الصلاحيات المرتبطة بالدور
        $permissions = Permission::whereIn('id', $request->permissions)->get();
        $role->syncPermissions($permissions);

        return response()->json([
            'message' => 'Role updated successfully',
            'role' => $role
        ]);
    }

    public function destroy($roleId)
    {
        // جلب الدور مع الصلاحيات
        $role = Role::with('permissions')->findOrFail($roleId);

        // إزالة الصلاحيات المرتبطة بالدور قبل حذفه
        $role->syncPermissions([]);

        // حذف الدور
        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully'
        ]);
    }
}
