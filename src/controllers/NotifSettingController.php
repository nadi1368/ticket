<?php

namespace hesabro\ticket\controllers;

use hesabro\notif\controllers\SettingController;
use hesabro\ticket\TicketModule;

class NotifSettingController extends SettingController
{
    protected ?string $group = 'tickets';

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);

        $this->setViewPath('@hesabro/notif/views/setting');

        $this->events = TicketModule::getNotifEvents();
    }
}