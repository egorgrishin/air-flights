<?php
declare(strict_types=1);

namespace App\Core\Commands;

use Seeders\AirportCompanySeeder;

class SeedCommand
{
    public function run(): void
    {
        (new AirportCompanySeeder())->run();
    }
}