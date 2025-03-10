<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Create roles if they don’t already exist
        Role::firstOrCreate(['name' => 'administrator']);
        Role::firstOrCreate(['name' => 'editor']);
        Role::firstOrCreate(['name' => 'executive']);
    }
}
