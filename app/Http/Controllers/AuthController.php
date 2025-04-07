<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Exception;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            if (auth()->user()->role !== 'super_admin') {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'role' => 'required|in:super_admin,team_lead,hr,user',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            // تعيين الدور إذا كان موجودًا
            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $user->assignRole($role);
            }

            return response()->json(['message' => 'User registered successfully'], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error in user registration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // public function login(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'email' => 'required|email',
    //             'password' => 'required',
    //         ]);

    //         $user = User::where('email', $request->email)->first();

    //         if (!$user || !Hash::check($request->password, $user->password)) {
    //             throw ValidationException::withMessages([
    //                 'email' => ['Invalid credentials.'],
    //             ]);
    //         }

    //         $token = $user->createToken('authToken')->plainTextToken;

    //         return response()->json([
    //             'id' => $user->id,
    //             'name' => $user->name,
    //             'email' => $user->email,
    //             'type' => $user->type ?? 'user',
    //             'preferred_locale' => $user->preferred_locale ?? 'en',
    //             'gender' => $user->gender ?? 'unknown',
    //             'phone' => $user->phone ?? '',
    //             'image' => $user->image ? asset($user->image) : 'https://example.com/default-avatar.jpg',
    //             'token' => $token,
    //             'roles' => $user->roles()->with(['permissions' => function ($query) {
    //                 $query->select('id', 'name');
    //             }])->get()->map(function ($role) {
    //                 return [
    //                     'id' => $role->id,
    //                     'name' => $role->name,
    //                     'permissions' => $role->permissions->map(function ($permission) use ($role) {
    //                         return [
    //                             'id' => $permission->id,
    //                             'name' => $permission->name,
    //                         ];
    //                     })
    //                 ];
    //             }),
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => 'Something went wrong',
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

public function login(Request $request)
{
    try {
        $request->validate([
            'login' => 'required', // يمكن أن يكون بريدًا إلكترونيًا أو رقم هاتف
            'password' => 'required',
        ]);

        // البحث عن المستخدم بواسطة البريد الإلكتروني أو رقم الهاتف
        $user = User::where('email', $request->login)
                    ->orWhere('phone', $request->login)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Invalid credentials.'],
            ]);
        }

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'type' => $user->type ?? 'user',
            'preferred_locale' => $user->preferred_locale ?? 'en',
            'gender' => $user->gender ?? 'unknown',
            'phone' => $user->phone ?? '',
            'image' => $user->image ? asset($user->image) : 'https://example.com/default-avatar.jpg',
            'token' => $token,
            'roles' => $user->roles()->with(['permissions' => function ($query) {
                $query->select('id', 'name');
            }])->get()->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->map(function ($permission) use ($role) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                        ];
                    })
                ];
            }),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Something went wrong',
            'message' => $e->getMessage(),
        ], 500);
    }
}


    // تسجيل الخروج
    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return response()->json(['message' => 'Logged out successfully']);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error in logout process',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
