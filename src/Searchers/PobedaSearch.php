<?php
declare(strict_types=1);

namespace App\Searchers;

use App\Contracts\SearcherContract;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class PobedaSearch implements SearcherContract
{
    public const CODE = 'Pobeda';

    public function getCode(): string
    {
        return self::CODE;
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function run(string $dep, string $arr, DateTime $dateTime): ?float
    {
        $date = $dateTime->format('d.m.Y');
        $uri = 'https://ticket.pobeda.aero/websky/json/search-variants-mono-brand-cartesian';
        $client = new Client();
        $response = $client->post($uri, [
            'form_params' => [
                'searchGroupId'         => 'standard',
                'segmentsCount'         => 1,
                'date'                  => [$date],
                'origin-city-code'      => [$dep],
                'destination-city-code' => [$arr],
                'adultsCount'           => 1,
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        $headers = $response->getHeaders();
        $contentType = $headers['content-type'] ?? $headers['Content-Type'];
        if ($response->getStatusCode() !== 200 || in_array('text/html', $contentType)) {
            throw new Exception('Error Pobeda');
        }

        $data = json_decode($response->getBody()->getContents(), true);
        if (($data['result'] ?? '0') !== 'ok') {
            throw new Exception('Error Pobeda');
        }

        return $this->parse($data) ?: null;
    }

    /**
     * @throws Exception
     */
    private function parse(array $data): float
    {
        if (empty($data['prices'])) {
            throw new Exception('Parse Error Pobeda [prices]');
        }

        $data = $data['prices'][0];
        $minPrice = 10 ** 6;
        foreach ($data as $prices) {
            $prices = array_column($prices, 'price');
            $prices = array_map('intval', $prices);
            $minPrice = min($minPrice, ...$prices);
        }

        return $minPrice;
    }
}
