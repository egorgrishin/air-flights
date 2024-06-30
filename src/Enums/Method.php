<?php
declare(strict_types=1);

namespace App\Enums;

enum Method: string
{
    case Send       = 'sendMessage';
    case Edit       = 'editMessageText';
    case Delete     = 'deleteMessage';
    case SendAnswer = 'answerCallbackQuery';
}
