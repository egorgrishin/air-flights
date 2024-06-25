<?php
declare(strict_types=1);

namespace App\Searchers;

use App\Contracts\SearcherContract;
use App\Exceptions\SearcherError;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class SmartaviaSearch implements SearcherContract
{
    public const CODE = 'SmartAvia';
    private const URI = 'https://flysmartavia.com/search/calendar';

    /**
     * Возвращает код авиакомпании
     */
    public function getCode(): string
    {
        return self::CODE;
    }

    /**
     * Возвращает цену на авиабилет с указанными параметрами
     * @throws GuzzleException
     * @throws SearcherError
     */
    public function getPrice(string $dep, string $arr, DateTime $dt): ?float
    {
        $response = $this->sendRequest($dep, $arr, $dt);
        $data = $this->getResponseData($response);

        return $this->parse($dep, $arr, $dt, $data) ?: null;
    }

    /**
     * Отправляет запрос к API а/к "SmartAvia" для получения цен на авиабилеты
     * @throws GuzzleException
     */
    private function sendRequest(string $dep, string $arr, DateTime $dt): ResponseInterface
    {
        $client = new Client();
        return $client->post(self::URI, [
            'body'    => http_build_query([
                'origin'              => $dep,
                'destination'         => $arr,
                'calendar_date_start' => $dt->format('Y-m-d'),
                'calendar_date_end'   => $dt->format('Y-m-d'),
            ]),
            'headers' => [
                "Accept"       => "application/json",
                "Content-Type" => "application/x-www-form-urlencoded",
            ],
        ]);
    }

    /**
     * Возвращает тело ответа в виде ассоциативного массива
     * @throws SearcherError
     */
    private function getResponseData(ResponseInterface $response): array
    {
        if ($response->getStatusCode() !== 200) {
            throw new SearcherError('Error Smartavia');
        }

        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }

    /**
     * Возвращает цену из данных, вернувшихся из API
     * @throws SearcherError
     */
    private function parse(string $dep, string $arr, DateTime $dt, array $data): float
    {
        if (($data['status'] ?? '0') !== 'ok') {
            throw new SearcherError("Error Smartavia: [status] is not equal [ok]");
        }
        if (empty($data['data'])) {
            throw new SearcherError("Error Smartavia: [data] are empty");
        }

        $data = $data['data'];
        $key = "$dep-$arr";
        if (empty($data[$key])) {
            throw new SearcherError("Error Smartavia: [$key] are empty");
        }

        $data = $data[$key];
        $date = $dt->format('Y-m-d');
        if (empty($data[$date])) {
            throw new SearcherError("Error Smartavia: [$date] are empty");
        }

        $data = $data[$date];
        if (empty($data['label'])) {
            throw new SearcherError("Error Smartavia: [label] are empty");
        }

        $data = $data['label'];
        if (empty($data['value'])) {
            throw new SearcherError("Error Smartavia: [value] are empty");
        }

        return (float) str_replace([' ', '₽'], '', $data['value']);
    }
}