<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class WoodWiseDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            WoodWiseReferenceSeeder::class,
            WoodWiseAuthSeeder::class,
            WoodWiseOperationalSeeder::class,
            WoodWiseMeasurementsSeeder::class,
        ]);
    }
}
