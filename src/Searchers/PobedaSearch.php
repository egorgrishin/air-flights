<?php
declare(strict_types=1);

namespace App\Searchers;

use App\Contracts\SearcherContract;
use App\Core\Container;
use App\Exceptions\SearcherParseError;
use App\Exceptions\SearcherResponseError;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class PobedaSearch implements SearcherContract
{
    public const  CODE = 'Pobeda';
    private const URI  = 'https://ticket.pobeda.aero/websky/json/search-variants-mono-brand-cartesian';

    /**
     * Возвращает код авиакомпании
     */
    public function getCode(): string
    {
        return self::CODE;
    }

    /**
     * Возвращает цену на авиабилет с указанными параметрами
     * @throws GuzzleException|SearcherParseError|SearcherResponseError
     */
    public function getPrice(string $dep, string $arr, DateTime $dt): ?float
    {
        $response = $this->sendRequest($dep, $arr, $dt);
        $data = $this->getResponseData($response);

        return $this->parse($data) ?: null;
    }

    /**
     * Отправляет запрос к API а/к "Победа" для получения цен на авиабилеты
     * @throws GuzzleException
     */
    private function sendRequest(string $dep, string $arr, DateTime $dt): ResponseInterface
    {
        $client = new Client();
        return $client->post(self::URI, [
            RequestOptions::ALLOW_REDIRECTS => false,
            RequestOptions::FORM_PARAMS => [
                'searchGroupId'         => 'standard',
                'segmentsCount'         => 1,
                'date'                  => [$dt->format('d.m.Y')],
                'origin-city-code'      => [$dep],
                'destination-city-code' => [$arr],
                'adultsCount'           => 1,
            ],
            RequestOptions::HEADERS     => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8',
                'User-Agent'   => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
            ],
        ]);
    }

    /**
     * Возвращает тело ответа в виде ассоциативного массива
     * @throws SearcherResponseError
     */
    private function getResponseData(ResponseInterface $response): array
    {
        $headers = $response->getHeaders();
        $contentType = $headers['content-type'] ?? $headers['Content-Type'];
        if ($response->getStatusCode() !== 200 || in_array('text/html', $contentType)) {
            Container::logger()->error($response->getBody()->getContents());
            throw new SearcherResponseError('Error Pobeda response');
        }

        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }

    /**
     * Возвращает цену из данных, вернувшихся из API
     * @throws SearcherParseError
     */
    private function parse(array $data): ?float
    {
        if (($data['result'] ?? '0') !== 'ok') {
            throw new SearcherParseError("Error Pobeda: [result] is not equal [ok]");
        }
        if (empty($data['prices'])) {
            throw new SearcherParseError("Error Pobeda: [prices] are empty");
        }

        // Индекс 0 - цены на билеты "туда". Индекс 1 - на билеты "обратно"
        // В приложении есть только билеты в одну сторону, поэтому используем только индекс 0
        $data = $data['prices'][0];
        $minPrice = PHP_INT_MAX;
        foreach ($data as $prices) {
            $prices = array_column($prices, 'price');
            $prices = array_map('floatval', $prices);
            $minPrice = min($minPrice, ...$prices);
        }
        return $minPrice == PHP_INT_MAX ? null : $minPrice;
    }
}
