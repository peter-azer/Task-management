<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            "name" => "William Kurnia Mulyadi Putra",
            "email" => "william@email.com",
            "password" => bcrypt("password"),
            "image_path" => "image/user/4.png",
        ]);

        User::create([
            "name" => "Sevien",
            "email" => "sevien@email.com",
            "password" => bcrypt("password"),
            "image_path" => "image/user/1.png",
        ]);

        User::create([
            "name" => "Farhan Ardiansyah",
            "email" => "farhan@email.com",
            "password" => bcrypt("password")
        ]);

        User::create([
            "name" => "M. Daffa Syamsuddin",
            "email" => "daffa@email.com",
            "password" => bcrypt("password")
        ]);

        User::create([
            "name" => "Filipus Bramantyo Meridivitanto",
            "email" => "filipus@email.com",
            "password" => bcrypt("password"),
            "image_path" => "image/user/2.jpg",
        ]);

        User::create([
            "name" => "Guido Owen Lwinatoro",
            "email" => "owen@email.com",
            "password" => bcrypt("password"),
            "image_path" => "image/user/3.jpg",
        ]);

        User::create([
            "name" => "Test Name",
            "email" => "test@email.com",
            "password" => bcrypt("password"),
        ]);

        User::create([
            "name" => "Test Second Name",
            "email" => "test2@email.com",
            "password" => bcrypt("password"),
            "is_active" => false,
        ]);
    }
}
