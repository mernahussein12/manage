<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Models\Department;

class UserController extends Controller
{


    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6',
        'phone' => 'nullable|string|max:20',
        'role_id' => 'required|exists:roles,id',
        'department_id' => 'required|exists:departments,id',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'phone' => $request->phone,
        'role_id' => $request->role_id,
        'department_id' => $request->department_id,
    ]);

    $user->load('department');

    $role = Role::find($request->role_id);
    if ($role) {
        $user->assignRole($role->name);
    }

    return response()->json([
        'message' => 'User created successfully',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'department' => [
                'id' => $user->department->id ?? null, // ✅ تجنب null
                'name' => $user->department->name ?? null, // ✅ تجنب null
            ],
            'role' => $user->getRoleNames()->first()
        ],
    ], 201);
}

    // public function index()
    // {
    //     // جلب جميع المستخدمين مع أدوارهم وأقسامهم، باستثناء "super_admin"
    //     $users = User::with(['roles', 'department'])
    //         ->whereDoesntHave('roles', function ($query) {
    //             $query->where('name', 'super_admin');
    //         })
    //         ->get();

    //     return response()->json([
    //         'users' => $users->map(function ($user) {
    //             return [
    //                 'id' => $user->id,
    //                 'name' => $user->name,
    //                 'email' => $user->email,
    //                 'phone' => $user->phone,
    //                 'department' => $user->department ? $user->department->name : null, // ✅ جلب اسم القسم
    //                 'role' => $user->getRoleNames()->first() // ✅ جلب اسم الدور
    //             ];
    //         })
    //     ]);
    // }

    public function index()
{
    $authUser = auth()->user(); // المستخدم المسجل حاليًا

    $usersQuery = User::with(['roles', 'department']);

    // إذا لم يكن المستخدم "super_admin"، نخفي المستخدمين الذين لديهم هذا الدور
    if (!$authUser->hasRole('super_admin')) {
        $usersQuery->whereDoesntHave('roles', function ($query) {
            $query->where('name', 'super_admin');
        });
    }

    $users = $usersQuery->get();

    return response()->json([
        'users' => $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'department' => $user->department ? $user->department->name : null, // ✅ جلب اسم القسم
                'role' => $user->getRoleNames()->first() // ✅ جلب اسم الدور
            ];
        })
    ]);
}

    public function show($id)
    {
        // جلب المستخدم مع دوره وقسمه
        $user = User::with(['roles', 'department'])->find($id);

        // التحقق مما إذا كان المستخدم موجودًا
        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'department' => $user->department ? $user->department->name : null, // ✅ جلب اسم القسم
            'role' => $user->getRoleNames()->first() // ✅ جلب اسم الدور
        ]);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if ($user->hasRole('super_admin')) {
            return response()->json([
                'message' => 'Cannot delete Super Admin'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    public function getRoles()
    {
        return response()->json([
            'roles' => Role::all()->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->map(function ($permission) use ($role) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name . ' (' . $role->name . ')', // تمييز كل برميشن بناءً على الدور
                        ];
                    })
                ];
            }),
        ]);
    }



    public function getTeamByDepartment(Request $request): JsonResponse
    {
        try {
            // التحقق من وجود department_id في الطلب
            if (!$request->has('department')) {
                return response()->json([
                    'message' => 'يرجى تحديد معرف القسم (department_id)'
                ], 400);
            }

            $departmentId = $request->department;

            // التحقق مما إذا كان القسم موجودًا في قاعدة البيانات
            $department = Department::find($departmentId);

            if (!$department) {
                return response()->json([
                    'message' => 'القسم غير موجود'
                ], 404);
            }

            // جلب جميع المستخدمين الذين ينتمون إلى الـ department_id
            $team = User::where('department_id', $departmentId)
                ->select('id', 'name')
                ->get();

            return response()->json([
                'message' => "تم جلب الفريق بنجاح للقسم: {$department->name}",
                'team' => $team
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء جلب الفريق',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getTeamLeaders(Request $request): JsonResponse
    {
        $user = auth()->user(); // جلب المستخدم الحالي
        $departmentId = $request->query('department_id');

        // قائمة الأدوار الخاصة بقادة الفرق
        $teamLeadRoles = [
            'team_lead_sales',
            'team_lead_developer',
            'team_lead_marketing',
            'team_lead_technical',
            'team_lead_hosting'
        ];

        // إذا كان المستخدم سوبر أدمن، يتم جلب جميع قادة الفرق
        if ($user->role === 'super_admin') {
            $teamLeaders = User::whereIn('role', $teamLeadRoles)
                ->select('id as value', 'name as label', 'role', 'department_id')
                ->get();

            return response()->json([
                'message' => "تم جلب جميع قادة الفرق بنجاح",
                'team_leaders' => $teamLeaders
            ], 200);
        }

        // التحقق من وجود department_id في الطلب
        if (!$departmentId) {
            return response()->json([
                'message' => 'يجب تحديد معرف القسم (department_id)',
                'team_leaders' => []
            ], 400);
        }

        // المستخدم العادي يحصل فقط على التيم ليدرز الخاصين بقسمه
        $teamLeaders = User::where('department_id', $departmentId)
            ->whereIn('role', $teamLeadRoles)
            ->select('id as value', 'name as label', 'role')
            ->get();

        return response()->json([
            'message' => "تم جلب القادة بنجاح للقسم ID: $departmentId",
            'team_leaders' => $teamLeaders
        ], 200);
    }



    public function search(Request $request)
    {
        $user = Auth::user();

        if (!$user || !in_array($user->role, ['super_admin'])) {
            return response()->json(['message' => 'غير مصرح لك بتنزيل هذا الملف'], 403);
        }

        $query = User::query()->with('roles');

        // البحث بالاسم
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // البحث بالبريد الإلكتروني
        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        // البحث برقم الهاتف
        if ($request->has('phone')) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }

        // التصفية حسب القسم
        if ($request->has('department')) {
            $query->where('department', $request->department);
        }

        // التصفية حسب الدور
        if ($request->has('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // تنفيذ البحث وإرجاع البيانات
        $users = $query->get();

        return response()->json([
            'message' => 'تم جلب المستخدمين بنجاح',
            'users' => $users
        ]);
    }
}
