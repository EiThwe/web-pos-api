<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::factory()->create([
            "name" => "Thwe Thwe",
            "email" => "tth@gmail.com",
            "password" => Hash::make("11223344"),
        ]);
        User::factory(5)->create();
    }
}
