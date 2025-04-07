<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    // public function handle(Request $request, Closure $next, $role, $department = null): Response
    // {
    //     // التحقق من أن المستخدم مسجّل الدخول ولديه الدور المطلوب
    //     // if (!$request->user() || !$request->user()->hasRole($role)) {
    //     //     return response()->json(['message' => 'Unauthorized'], 403);
    //     // }



    //     $user = auth()->user();

    //     if (!$user) {
    //         return response()->json(['message' => 'يجب عليك تسجيل الدخول'], 401);
    //     }

    //     if ($user->role !== $role) {
    //         return response()->json([
    //             'message' => 'غير مسموح لك بالوصول',
    //             'role' => $user->role, // ديباج لمشاهدة الدور الفعلي للمستخدم
    //             'required_role' => $role
    //         ], 403);
    //     }

    //     if ($department && $user->department !== $department) {
    //         return response()->json([
    //             'message' => 'ليس لديك صلاحية على هذا القسم',
    //             'user_department' => $user->department,
    //             'required_department' => $department
    //         ], 403);
    //     }
    //     return $next($request);
    // }



    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'يجب عليك تسجيل الدخول'], 401);
        }

        if (!$user->hasAnyRole($roles)) {
            return response()->json([
                'message' => 'غير مسموح لك بالوصول',
                'role' => $user->getRoleNames(), // تصحيح طريقة جلب الأدوار
                'required_roles' => $roles
            ], 403);
        }

        return $next($request);
    }

    }



