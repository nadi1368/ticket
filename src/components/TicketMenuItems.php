<?php

namespace hesabro\ticket\components;

use Yii;

class TicketMenuItems
{
    public static function items()
    {
        return [
            [
                'group' => 'tickets',
                'label' => Yii::t('tickets', 'Tickets'),
                'icon' => '	fal fa-ticket',
                'url' => ['/tickets']
            ],
            [
                'group' => 'ticket-departments',
                'label' => Yii::t('tickets', 'Tickets Departments'),
                'icon' => 'fal fa-ticket',
                'url' => ['/tickets/tickets-departments']
            ],
        ];
    }

}