<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            PackageSeeder::class,
            ConsoleSeeder::class,
            PaymentMethodSeeder::class,
            ContentSeeder::class,
            EmailTemplateSeeder::class,
            AdminSeeder::class,
        ]);
    }
}
