<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        if (!$user->hasRole('super_admin')) {
            return response()->json(['message' => 'غير مصرح لك بعرض هذه البيانات'], 403);
        }

        $departments = Department::all()->map(function ($department) {
            return [
                'value' => (string) $department->id,
                'label' => $department->name,
            ];
        });

        return response()->json($departments);
    }
}
