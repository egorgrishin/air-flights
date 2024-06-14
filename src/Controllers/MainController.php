<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\VO\Message;
use GuzzleHttp\Client;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class MainController
{
    public function __invoke(Request $request, Response $response, $args): Response
    {
        $message = new Message($request);
//        if ($message->text === '/start') {
            $this->sendHello($message);
//        }
//        Container::logger()->debug(json_encode($request->getParsedBody(), JSON_PRETTY_PRINT));
        $response->getBody()->write("Hello world!");
        return $response;
    }

    private function sendHello(Message $message)
    {
        $client = new Client();
        $token = Container::env()->get('TG_TOKEN');
        $url = "https://api.telegram.org/bot$token/sendMessage";

        $client->post($url, [
            'form_params' => [
                'chat_id' => $message->userId,
                'text'    => $message->text,
            ]
        ]);
    }
}