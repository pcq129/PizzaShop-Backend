<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // \App\Models\User::factory(10)->create();


        for ($i=0; $i < 15; $i++) {
            $super_admin = User::factory()->create([
                'first_name' => 'Super'.$i,
                'last_name' => 'Admin',
                'user_name' => 'SuperAdmin',
                'phone' => '9099102310',
                'address' => 'Ahmedabad, Gujarat',
                'email' => 'super_admi'.$i.'n@tatvasoft.com',
                'email_verified_at' => now(),
                'password' => bcrypt('#Harmit'),
                'remember_token' => Str::random(10),
            ]);

            $super_admin->assignRole('super_admin');


            $super_admin->save();
        }


        // $account_manager = User::factory()->create([
        //     'first_name' => 'Account',
        //     'last_name' => 'Manager',
        //     'user_name' => 'AccountManager',
        //     'phone' => '9099102310',
        //     'address' => 'Ahmedabad, Gujarat',
        //     'email' => 'accountmanager@tatvasoft.com',
        //     'email_verified_at' => now(),
        //     'password' => bcrypt('#Harmit'),
        //     'remember_token' => Str::random(10),
        // ]);

        // $account_manager->assignRole('account_manager');


        // $account_manager->save();



        // $chef = User::factory()->create([
        //     'first_name' => 'Kitchen',
        //     'last_name' => 'Chef',
        //     'user_name' => 'Chef',
        //     'phone' => '9099102310',
        //     'address' => 'Ahmedabad, Gujarat',
        //     'email' => 'chef@tatvasoft.com',
        //     'email_verified_at' => now(),
        //     'password' => bcrypt('#Harmit'),
        //     'remember_token' => Str::random(10),
        // ]);

        // $chef->assignRole('chef');


        // $chef->save();

    }



}
