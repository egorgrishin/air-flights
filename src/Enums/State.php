<?php
declare(strict_types=1);

namespace App\Enums;

enum State: string
{
    case StartSubscription = 'Новая подписка';
    case SubscriptionsList = 'Список подписок';
    case SubsSelect = 'ss';
    case SubsDelete = 'ds';
    case SelectDep = 'sda';
    case SelectArr = 'saa';
    case SelectMonth = 'sdm';
    case SelectDay = 'sdd';
    case AcceptMonitoring = 'accptm';
    case SuccessMonitoring = 'sccssm';
    case CancelMonitoring = 'cnclm';
}