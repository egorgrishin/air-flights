<?php
declare(strict_types=1);

namespace App\Core;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

final class Telegram
{
    public static function send(string $method, array $data): void
    {
        $uri = self::getUri($method);

//        try {
            (new Client())->post($uri, [
                'headers' => [
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);
//        } catch (GuzzleException $exception) {
//            Container::logger()->error($exception);
//        }
    }

    private static function getUri(string $method)
    {
        $token = Container::env()->get('TG_TOKEN');
        return "https://api.telegram.org/bot$token/$method";
    }
}