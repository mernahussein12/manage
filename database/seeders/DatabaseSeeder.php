<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // استدعاء Seeder الخاص بالأدوار
        $this->call(RoleSeeder::class);

        // إنشاء Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'), // غيري الباسورد حسب الحاجة
        ]);

        // تعيين دور السوبر أدمن
        $role = Role::where('name', 'super_admin')->first();
        if ($role) {
            $superAdmin->assignRole($role);
        }

        echo "✅ Super Admin Created Successfully!\n";
    }
}
