<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            'Marketing',
            'Sales',
            'Technical Support',
            'Hosting',
            'HR',
            'developer'
        ];

        foreach ($departments as $name) {
            Department::create(['name' => $name]);
        }
    }
}
