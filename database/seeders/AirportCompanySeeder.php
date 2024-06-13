<?php
declare(strict_types=1);

namespace Seeders;

use App\Models\Airport;
use App\Models\AirportCompany;
use App\Models\Company;

class AirportCompanySeeder
{
    private const COMPANIES = [
        ['title' => 'Победа', 'file' => 'pobeda.json'],
        ['title' => 'Smartavia', 'file' => 'smartavia.json'],
    ];

    public function run()
    {
        $airports = [];
        foreach (self::COMPANIES as $company) {
            $companyModel = (new Company($company['title']))->create();
            $companyAirports = $this->getAirports($company['file']);
            foreach ($companyAirports as $airport) {
                if (empty($airports[$airport['iataCode']])) {
                    $airports[$airport['iataCode']] = (new Airport(
                        $airport['iataCode'],
                        $airport['metropolitanCode'],
                        $airport['name']
                    ))->create();
                }
                $airport = $airports[$airport['iataCode']];
                (new AirportCompany($airport->code, $companyModel->id))->create();
            }
        }
    }

    private function getAirports(string $file): array
    {
        return json_decode(
            file_get_contents(__DIR__ . "/data/airports/$file"),
            true
        );
    }
}