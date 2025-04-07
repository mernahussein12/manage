<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class CheckTeamLeadDepartment
{
    // public function handle(Request $request, Closure $next, $department)
    // {
    //     if (auth()->check()) {
    //         $user = auth()->user();

    //         // ✅ السماح لـ Super Admin بالمرور دون قيود، لأنه لا يمتلك department
    //         if ($user->role === 'super_admin') {
    //             return $next($request);
    //         }

    //         // ✅ السماح لـ Team Lead فقط إذا كان في نفس القسم
    //         if ($user->role === 'team_lead') {
    //             // 🛑 التحقق مما إذا كان لديه قسم في الأساس
    //             if (!$user->department) {
    //                 return response()->json([
    //                     'message' => 'خطأ: لا يوجد قسم معين لهذا المستخدم.',
    //                     'role' => $user->role
    //                 ], 403);
    //             }

    //             // 🔍 مقارنة القسم مع المطلوب في الميدل وير
    //             if ($user->department !== $department) {
    //                 return response()->json([
    //                     'message' => 'ليس لديك صلاحية للوصول إلى هذا القسم',
    //                     'user_department' => $user->department,
    //                     'requested_department' => $department
    //                 ], 403);
    //             }

    //             return $next($request);
    //         }
    //     }

    //     return response()->json(['message' => 'ليس لديك صلاحية للوصول إلى هذا القسم'], 403);
    // }
    public function handle(Request $request, Closure $next, $department)
{
    if (!auth()->check()) {
        return response()->json(['message' => 'يجب عليك تسجيل الدخول'], 401);
    }

    $user = auth()->user(); // ✅ تأكد من جلب بيانات المستخدم بشكل صحيح

    Log::info('Middleware CheckTeamLeadDepartment:', [
        'user_id' => $user->id,
        'user_role' => $user->role,
        'user_department' => $user->department ?? 'N/A',
        'requested_department' => $department
    ]);

    // ✅ السماح لـ Super Admin بالوصول بدون قيود
    if ($user->role === 'super_admin') {
        return $next($request);
    }

    // ✅ السماح لـ Team Lead إذا كان في نفس القسم
    if ($user->role === 'team_lead' && $user->department === $department) {
        return $next($request);
    }

    // ✅ السماح لـ Team Lead في قسم `sales` بإرسال الطلبات
    if ($user->role === 'team_lead' && $user->department === 'sales') {
        return $next($request);
    }

    return response()->json([
        'message' => 'ليس لديك صلاحية للوصول إلى هذا القسم',
        'user_role' => $user->role,
        'user_department' => $user->department ?? 'N/A',
        'requested_department' => $department
    ], 403);
}

}


