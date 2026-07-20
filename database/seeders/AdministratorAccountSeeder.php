<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class AdministratorAccountSeeder extends Seeder {
    use WithoutModelEvents;
    
    public function run(): void {
        DB::table("users")->insert([
            "fullname" => "SyncTrack Administrator",
            "username" => "admin",
            "email" => "admin@synctrack.com",
            "password" => Hash::make("admin123"),
            "nric" => "010203040567",
            "contact" => "0123456789",
            "is_admin" => true,
            "created_at" => now(),
            "updated_at" => now(),
        ]);
    }
}