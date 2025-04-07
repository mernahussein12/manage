<?php

// namespace Database\Seeders;

// use Illuminate\Database\Seeder;
// use Spatie\Permission\Models\Role;
// use Spatie\Permission\Models\Permission;

// class RoleSeeder extends Seeder
// {
//     public function run()
//     {
//         // تحديد الـ guard_name ليكون sanctum
//         $guardName = 'sanctum';

//         // إنشاء الصلاحيات مع تحديد الـ guard
//         $permissions = [
//             'create users',
//             'edit users',
//             'delete users',
//             'view users',
//             'manage projects',
//             'assign tasks',
//             'approve requests',
//         ];

//         foreach ($permissions as $permission) {
//             Permission::firstOrCreate(
//                 ['name' => $permission, 'guard_name' => $guardName]
//             );
//         }

//         // إنشاء الأدوار مع تحديد الـ guard
//         $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => $guardName]);
//         $teamLead = Role::firstOrCreate(['name' => 'team_lead', 'guard_name' => $guardName]);
//         $hr = Role::firstOrCreate(['name' => 'hr', 'guard_name' => $guardName]);
//         $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => $guardName]);

//         // إعطاء Super Admin كافة الصلاحيات
//         $superAdmin->givePermissionTo(Permission::all());

//         // توزيع الصلاحيات على باقي الأدوار
//         $teamLead->givePermissionTo(['view users', 'assign tasks']);
//         $hr->givePermissionTo(['view users', 'approve requests']);
//         $user->givePermissionTo(['view users']);
//     }
// }
