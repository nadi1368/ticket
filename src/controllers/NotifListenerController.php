<?php

namespace hesabro\ticket\controllers;

use hesabro\notif\controllers\ListenerController;
use hesabro\ticket\TicketModule;

class NotifListenerController extends ListenerController
{
    protected ?string $group = 'tickets';

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);

        $this->setViewPath('@hesabro/notif/views/listener');

        $this->events = TicketModule::getNotifEvents();
    }
}
