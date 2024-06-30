<?php
declare(strict_types=1);

namespace App\Core;

use App\Enums\Method;
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

    /**
     * Отправляет запрос в телеграм
     */
    public function send(Method $method, array $data): void
    {
        try {
            $this->client->post($method->value, [
                'headers' => [
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json'    => $data,
            ]);
        } catch (GuzzleException) {
            //
        }
    }

    /**
     * Возвращает URI для отправления запросов в Telegram
     */
    private function getUri(): string
    {
        $token = Container::env()->get('TG_TOKEN');
        return "https://api.telegram.org/bot$token/";
    }
}