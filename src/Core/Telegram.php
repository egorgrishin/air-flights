<?php
declare(strict_types=1);

namespace App\Core;

use App\Enums\TelegramMethod;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

final class Telegram
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => $this->getUri(),
        ]);
    }

    public function send(TelegramMethod $method, array $data): void
    {
        try {
            Container::logger()->debug(json_encode($data, JSON_PRETTY_PRINT));
            $this->client->post($method->value, [
                'headers' => [
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json'    => $data,
            ]);
        } catch (GuzzleException $exception) {
            Container::logger()->error($exception->getResponse());
        }
    }

    private function getUri(): string
    {
        $token = Container::env()->get('TG_TOKEN');
        return "https://api.telegram.org/bot$token/";
    }
}