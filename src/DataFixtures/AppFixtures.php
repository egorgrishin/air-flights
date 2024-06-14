<?php

namespace App\DataFixtures;

use App\Entity\Airport;
use App\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private const COMPANIES = [
        ['title' => 'Победа', 'file' => 'pobeda.json'],
        ['title' => 'Smartavia', 'file' => 'smartavia.json'],
    ];

    public function load(ObjectManager $manager): void
    {
        $airports = [];
        foreach (self::COMPANIES as $company) {
            $companyEntity = new Company();
            $companyEntity->setTitle($company['title']);
            $manager->persist($companyEntity);

            $companyAirports = $this->getAirports($company['file']);
            foreach ($companyAirports as $airport) {
                if (empty($airports[$airport['iataCode']])) {
                    $airportEntity = (new Airport())
                        ->setCode($airport['iataCode'])
                        ->setCityCode($airport['metropolitanCode'])
                        ->setTitle($airport['name']);
                    $manager->persist($airportEntity);
                    $airports[$airport['iataCode']] = $airportEntity;
                }
                $airport = $airports[$airport['iataCode']];
                $companyEntity->addAirport($airport);
            }
        }
        $manager->flush();
    }

    private function getAirports(string $file): array
    {
        return json_decode(
            file_get_contents(__DIR__ . "/airports/$file"),
            true
        );
    }
}
