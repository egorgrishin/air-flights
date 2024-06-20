<?php
declare(strict_types=1);

namespace App\Searchers;

use App\Contracts\SearcherContract;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class SmartaviaSearch implements SearcherContract
{
    public const CODE = 'SmartAvia';

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
        $date = $dateTime->format('Y-m-d');
        $params = [
            'origin'              => $dep,
            'destination'         => $arr,
            'calendar_date_start' => $date,
            'calendar_date_end'   => $date,
        ];
        $body = $this->toBody($params);

        $uri = 'https://flysmartavia.com/search/calendar';
        $client = new Client();
        $response = $client->post($uri, [
            'body'    => $body,
            'headers' => [
                "Accept"       => "application/json",
                "Content-Type" => "application/x-www-form-urlencoded",
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new Exception('Error Smartavia');
        }
        $data = json_decode($response->getBody()->getContents(), true);
        if (($data['status'] ?? '0') !== 'ok') {
            throw new Exception('Error Smartavia');
        }

        // Может быть 0
        return $this->parse($data, $params) ?: null;
    }

    private function toBody(array $params): string
    {
        $body = [];
        foreach ($params as $key => $value) {
            $body[] = "$key=$value";
        }
        return implode('&', $body);
    }

    /**
     * @throws Exception
     */
    private function parse(array $data, array $params): float
    {
        if (empty($data['data'])) {
            throw new Exception('Parse Error Smartavia [data]');
        }

        $data = $data['data'];
        $key = $params['origin'] . '-' . $params['destination'];
        if (empty($data[$key])) {
            throw new Exception('Parse Error Smartavia [$key]');
        }

        $data = $data[$key];
        $date = $params['calendar_date_start'];
        if (empty($data[$date])) {
            throw new Exception('Parse Error Smartavia [$date]');
        }

        $data = $data[$date];
        if (empty($data['label'])) {
            throw new Exception('Parse Error Smartavia [label]');
        }

        $data = $data['label'];
        if (empty($data['value'])) {
            throw new Exception('Parse Error Smartavia [value]');
        }

        return (float) str_replace([' ', '₽'], '', $data['value']);
    }
}