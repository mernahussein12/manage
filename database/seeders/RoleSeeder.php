<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Permission::query()->delete();

        $permissions = [
            'Add_project_sales', 'All_request_sales', 'Add_request_sales', 'All_sales', 'show_sales', 'delete_sales',

            'Add_project_developer', 'All_request_developer', 'Status_request_developer', 'All_developer', 'show_developer', 'delete_developer',

            'Add_project_marketing', 'All_request_marketing', 'Status_request_marketing', 'All_marketing', 'show_marketing', 'delete_marketing',

            'Add_project_technical', 'All_technical', 'show_technical', 'edit_technical', 'delete_technical',

            'Add_project_hosting', 'All_hosting', 'show_hosting', 'edit_hosting', 'delete_hosting',

            'add_report', 'all_report',

            'Add_role', 'all_permissions', 'edit_role', 'all_role', 'Add_user', 'All_user', 'Show_user'
        ];

        // إضافة كافة الصلاحيات
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = [
            'super_admin', 'team_lead_sales', 'team_lead_developer', 'team_lead_marketing', 'team_lead_technical', 'team_lead_hosting',
            'sales', 'developer', 'marketing', 'technical', 'hosting', 'user'
        ];

        foreach ($roles as $role) {
            $$role = Role::firstOrCreate(['name' => $role]);
        }

        $team_lead_sales->givePermissionTo(['Add_project_sales', 'All_request_sales', 'Add_request_sales', 'All_sales', 'show_sales', 'delete_sales']);
        $sales->givePermissionTo(['Add_project_sales', 'All_request_sales', 'Add_request_sales']);

        $team_lead_developer->givePermissionTo(['Add_project_developer', 'All_request_developer', 'Status_request_developer', 'All_developer', 'show_developer', 'delete_developer']);
        $developer->givePermissionTo(['Add_project_developer', 'All_request_developer', 'Status_request_developer']);

        $team_lead_marketing->givePermissionTo(['Add_project_marketing', 'All_request_marketing', 'Status_request_marketing', 'All_marketing', 'show_marketing', 'delete_marketing']);
        $marketing->givePermissionTo(['Add_project_marketing', 'All_request_marketing', 'Status_request_marketing']);

        $team_lead_technical->givePermissionTo(['Add_project_technical', 'All_technical', 'show_technical', 'edit_technical', 'delete_technical']);
        $technical->givePermissionTo(['Add_project_technical', 'All_technical', 'show_technical', 'edit_technical', 'delete_technical']);

        $team_lead_hosting->givePermissionTo(['Add_project_hosting', 'All_hosting', 'show_hosting', 'edit_hosting', 'delete_hosting']);
        $hosting->givePermissionTo(['Add_project_hosting', 'All_hosting', 'show_hosting', 'edit_hosting', 'delete_hosting']);

        foreach (['super_admin', 'team_lead_sales', 'team_lead_developer', 'team_lead_marketing', 'team_lead_technical', 'team_lead_hosting', 'user'] as $role) {
            $$role->givePermissionTo(['add_report', 'all_report']);
        }

        $super_admin->givePermissionTo([
            'All_sales', 'show_sales',
            'All_developer', 'show_developer',
            'All_marketing', 'show_marketing',
            'All_request_developer', 'All_request_marketing', 'All_request_sales'
        ]);

        $super_admin->givePermissionTo([
            'Add_role', 'all_permissions', 'edit_role', 'all_role',
            'Add_user', 'All_user', 'Show_user'
        ]);
    }
}
