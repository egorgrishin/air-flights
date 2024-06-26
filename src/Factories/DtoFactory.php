<?php
declare(strict_types=1);

namespace App\Factories;

use App\Contracts\DtoContract;
use App\Dto\CallbackDto;
use App\Dto\TextDto;
use Psr\Http\Message\ServerRequestInterface as Request;

class DtoFactory
{
    /**
     * Возвращает DTO, сгенерированный из объекта Request
     */
    public static function make(Request $request): DtoContract
    {
        $body = (array) $request->getParsedBody();
        return match (true) {
            self::isTextMessage($body) => new TextDto($body),
            self::isCallback($body)    => new CallbackDto($body),
            default                    => self::makeTextDto404(),
        };
    }

    /**
     * Проверяет, что пользователь отправил текстовое сообщение
     */
    private static function isTextMessage(array $body): bool
    {
        return !empty($body['message'])
            && !empty($body['message']['chat']['id'])
            && !empty($body['message']['text']);
    }

    /**
     * Проверяет, что пользователь нажал на кнопку inline-клавиатуры
     */
    private static function isCallback(array $body): bool
    {
        return !empty($body['callback_query'])
            && !empty($body['callback_query']['from']['id'])
            && !empty($body['callback_query']['data'])
            && !empty($body['callback_query']['message']['message_id'])
            && !empty($body['callback_query']['id']);
    }

    /**
     * Возвращает сымитированное текстовое DTO, на котором сработает только обработчик 404
     */
    private static function makeTextDto404(): TextDto
    {
        return new TextDto([
            'message' => [
                'from' => [
                    'id' => '0',
                ],
                'text' => 'Error',
            ],
        ]);
    }
}