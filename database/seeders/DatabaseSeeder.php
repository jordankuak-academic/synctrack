<?php
namespace Database\Seeders;

use Database\Seeders\AdministratorAccountSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    use WithoutModelEvents;
    
    public function run(): void {
        $this->call([
            AdministratorAccountSeeder::class,
        ]);
    }
}
