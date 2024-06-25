<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Container;
use App\VO\Company;

final class CompanyRepository
{
    /**
     * Создает компанию
     *
     * @param Company $company
     * @return void
     */
    public function create(Company $company): void
    {
        $sql = <<<SQL
            INSERT INTO companies (code, title)
            VALUES (?, ?)
        SQL;

        Container::pdo()
            ->prepare($sql)
            ->execute([$company->code, $company->title]);
    }

    /**
     * Прикрепляет компанию к аэропорту
     * @param string $airportCode
     * @param string $companyCode
     * @return void
     */
    public function attachToAirport(string $airportCode, string $companyCode): void
    {
        $sql = <<<SQL
            INSERT INTO airport_company (airport_code, company_code)
            VALUES (?, ?)
        SQL;

        Container::pdo()
            ->prepare($sql)
            ->execute([$airportCode, $companyCode]);
    }
}