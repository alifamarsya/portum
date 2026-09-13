<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UnitKerjaSeeder::class,
            TicketRoleSeeder::class,
            UserSeeder::class,
            RefAkunSeeder::class,
            DummyDataSeeder::class,
        ]);
    }
}