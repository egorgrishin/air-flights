<?php
declare(strict_types=1);

namespace App\Enums;

enum State: string
{
    /** Запуск создания подписки */
    case StartSubscription = 'Start 🚀';

    /** Отображение списка подписок */
    case SubscriptionsList = 'Активные подписки ⚡️';

    /** Отображение инструкции */
    case Instruction = 'Инструкция 📜';

    /** Отображение списка подписок (постраничная навигация подписок и их удаление) */
    case SubsSelect = 'ss';

    /** Выбор города отправления в создании подписки */
    case SelectDep = 'sda';

    /** Выбор города прибытия в создании подписки */
    case SelectArr = 'saa';

    /** Выбор месяца вылета в создании подписки */
    case SelectMonth = 'sdm';

    /** Выбор дня вылета в создании подписки */
    case SelectDay = 'sdd';

    /** Подтверждение подписки */
    case AcceptMonitoring = 'accptm';

    /** Успешное создание подписки */
    case SuccessMonitoring = 'sccssm';

    /** Отмена создания подписки */
    case CancelMonitoring = 'cnclm';
}