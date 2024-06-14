<?php
declare(strict_types=1);

namespace App\Searchers;

use DateTime;
use GuzzleHttp\Client;

class PobedaSearch
{
    public function run(DateTime $dateTime)
    {
        $date = $dateTime->format('d.m.Y');
        $uri = 'https://ticket.pobeda.aero/websky/json/search-variants-mono-brand-cartesian';
        $client = new Client();
        $response = $client->post($uri, [
            'form_params' => [
                'searchGroupId'         => 'standard',
                'segmentsCount'         => 1,
                'date'                  => [$date],
                'origin-city-code'      => ['LED'],
                'destination-city-code' => ['CEK'],
                'adultsCount'           => 1,
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        $headers = $response->getHeaders();
        $contentType = $headers['content-type'] ?? $headers['Content-Type'];
        if ($response->getStatusCode() !== 200 || in_array('text/html', $contentType)) {
            dump('Error Pobeda');
            return;
        }

        $data = json_decode($response->getBody()->getContents(), true);
        if (($data['result'] ?? '0') !== 'ok') {
            dump('Error Pobeda', $data);
            return;
        }

        dump("Победа, СПБ-ЧЛБ, $date, от " . $this->parse($data));
    }

    private function parse(array $data)
    {
        if (empty($data['prices'])) {
            dd('Parse Error Pobeda [prices]');
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