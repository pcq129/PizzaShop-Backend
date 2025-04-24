<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        \App\Models\User::factory(1)->create();

        // \App\Models\users::factory()->create([
        //     'first_name' => fake()->firstName(),
        //     'last_name' => fake()->lastName(),
        //     'user_name' => fake()->userName(),
        //     'phone'=> fake()->phoneNumber(),
        //     'address'=> fake()->address(),
        //     'email' => fake()->unique()->safeEmail(),
        //     'email_verified_at' => now(),
        //     'password' => '$efewfaer', // password
        //     'remember_token' => Str::random(10),
        // ]);
    }
}
