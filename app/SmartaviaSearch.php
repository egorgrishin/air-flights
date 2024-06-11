<?php
declare(strict_types=1);

namespace App;

use GuzzleHttp\Client;

class SmartaviaSearch
{
    public function run()
    {
        $params = [
            'origin'              => 'LED',
            'destination'         => 'CEK',
            'calendar_date_start' => '2024-06-15',
            'calendar_date_end'   => '2024-06-15',
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
            dump('Error Smartavia');
        }
        $data = json_decode($response->getBody()->getContents(), true);
        if (($data['status'] ?? '0') !== 'ok') {
            dump('Error Smartavia');
        }

        // Может быть 0
        dd('Smartavia, СПБ-ЧЛБ, 15.06, от ' . $this->parse($data, $params));
    }

    private function toBody(array $params): string
    {
        $body = [];
        foreach ($params as $key => $value) {
            $body[] = "$key=$value";
        }
        return implode('&', $body);
    }

    private function parse(array $data, array $params)
    {
        if (empty($data['data'])) {
            dd('Parse Error Smartavia [data]');
        }

        $data = $data['data'];
        $key = $params['origin'] . '-' . $params['destination'];
        if (empty($data[$key])) {
            dd("Parse Error Smartavia [$key]");
        }

        $data = $data[$key];
        $date = $params['calendar_date_start'];
        if (empty($data[$date])) {
            dd("Parse Error Smartavia [$date]");
        }

        $data = $data[$date];
        if (empty($data['label'])) {
            dd("Parse Error Smartavia [label]");
        }

        $data = $data['label'];
        if (empty($data['value'])) {
            dd("Parse Error Smartavia [value]");
        }

        return (int) str_replace([' ', '₽'], '', $data['value']);
    }
}