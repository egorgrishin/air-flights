<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Telegram;
use App\Repositories\AirportRepository;
use App\Repositories\CompanyRepository;
use App\VO\Message;
use GuzzleHttp\Client;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class MainController
{
    public function __invoke(Request $request, Response $response, $args): Response
    {
        Container::logger()->debug(json_encode($request->getParsedBody(), JSON_PRETTY_PRINT));
//        $message = new Message($request);
//        if ($message->text === '/start') {
//            $this->sendHello($message);
//        } elseif ($message->text === 'Начать мониторинг') {
//            $this->sendMonit($message);
//        }
        $response->getBody()->write("Hello world!");
        return $response;
    }

    private function sendHello(Message $message)
    {
        $companies = (new CompanyRepository())->getAll();
        $text = "
Привет!
Я - бот Air Flights и я занимаюсь мониторингом цен на авиабилеты следующих компаний:
";
        foreach ($companies as $company) {
            $text .= "• " . $company->title . "\n";
        }

        Telegram::send('sendMessage', [
            'chat_id'      => $message->userId,
            'text'         => $text,
            'reply_markup' => [
                'keyboard'          => [
                    [
                        ['text' => 'Начать мониторинг'],
                    ],
                ],
                'one_time_keyboard' => true,
                'resize_keyboard'   => true,
            ],
        ]);
    }

    private function sendMonit(Message $message)
    {
        $airports = (new AirportRepository())->getAll();
        $airports = array_slice($airports, 0, 5);
        foreach ($airports as &$airport) {
            $airport = [[
                'text' => $airport->title,
                'callback_data' => "monit:$airport->code:0:5",
            ]];
        }

        Telegram::send('sendMessage', [
            'chat_id'      => $message->userId,
            'text'         => "Выберите аэропорт отправления",
            'reply_markup' => [
                'inline_keyboard'          => [
                    ...$airports,
                    [
                        ['text' => '<-', 'callback_data' => 'monit:<'],
                        ['text' => '->', 'callback_data' => 'monit:>'],
                    ]
                ],
//                'one_time_keyboard' => true,
//                'resize_keyboard'   => true,
            ],
        ]);
    }
}