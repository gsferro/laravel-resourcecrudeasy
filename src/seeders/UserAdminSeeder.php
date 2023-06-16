<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::firstOrCreate([
            'email'             => 'admin@admin.com',
        ], [
            'name'              => 'Admin',
            'password'          => Hash::make('$admin$'),
            'email_verified_at' => now(),
        ]);

        $role = Role::firstOrCreate(['name' => 'super-admin']);
        $user->assignRole($role);
    }
}
