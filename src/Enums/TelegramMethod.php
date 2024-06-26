<?php
declare(strict_types=1);

namespace App\Enums;

enum TelegramMethod: string
{
    case Send = 'sendMessage';
    case Edit = 'editMessageText';
    case Delete = 'deleteMessage';
    case SendAnswer = 'answerCallbackQuery';
}
