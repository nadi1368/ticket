<?php

namespace hesabro\ticket\jobs;

use console\job\MasterJob;
use hesabro\ticket\models\Tickets;
use Yii;
use yii\queue\RetryableJobInterface;

class SendTicketNotifJob extends MasterJob implements \yii\queue\JobInterface, RetryableJobInterface
{
    public $ticket_id;

    public function execute($queue)
    {
        parent::execute($queue);
        $ticket = Tickets::findOne($this->ticket_id);
        if($ticket){
            $ticket->send_notif = true;
            $ticket->setScenario(Tickets::SCENARIO_SEND);
            if($ticket->type == Tickets::TYPE_MASTER && !Yii::$app->client->isMaster() && !$ticket->department_id){
                $ticket->setScenario(Tickets::SCENARIO_SUPPORT);
            }
            $ticket->sendNotif();
        }
    }

    public function getTtr()
    {
        return 15 * 60;
    }

    public function canRetry($attempt, $error)
    {
        if ($attempt <= 3) {
            return true;
        } else {
            Yii::$app->queueSecondary->push(new self([
                'slaveId' => $this->slaveId,
                'ticket_id' => $this->ticket_id,
            ]));
        }
    }
}