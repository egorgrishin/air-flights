<?php
declare(strict_types=1);

namespace App\VO;

use Psr\Http\Message\ServerRequestInterface as Request;

final readonly class Message
{
    public int $userId;
    public string $text;

    public function __construct(Request $request)
    {
        $body = $request->getParsedBody();
        $this->userId = $body['message']['from']['id'];
        $this->text = $body['message']['text'];
    }
}