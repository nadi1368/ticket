<?php

use hesabro\ticket\models\Tickets;
use yii\helpers\Url;


$action = Yii::$app->controller->action->id;
$inbox_count = Tickets::countInbox();
$doing_count = Tickets::countInbox(Tickets::STATUS_DOING);

?>

<div class="d-flex gap-5">
    <div class="d-flex flex-column flex-grow-1">
        <ul class="nav nav-tabs nav-fill bg-white pt-3">
            <li class="nav-item">
                <a href="<?= Url::to(['inbox', 'TicketsSearch[status]' => Tickets::STATUS_ACTIVE]) ?>" class="nav-link <?= $action == 'inbox'  ? 'active' : '' ?>">
                    <i class="fa fa-download"></i>
                    <?= Yii::t("app", "Inbox") ?>
                </a>
            </li>
        </ul>
        <ul class="nav nav-tabs nav-fill bg-white mb-3 <?= $action == 'outbox' ? 'd-none' : '' ?>">
            <li class="nav-item">
                <a href="<?= Url::to(['inbox', 'TicketsSearch[status]' => Tickets::STATUS_ACTIVE]) ?>" class="nav-link <?= $action == 'inbox' && $searchModel->status == Tickets::STATUS_ACTIVE ? 'active' : '' ?>">
                    <?= Yii::t("app", "Inbox") ?>
                    <?php if ($inbox_count > 0) : ?>
                        <span class="badge badge-danger pull-left"><?= $inbox_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= Url::to(['inbox', 'TicketsSearch[status]' => Tickets::STATUS_DOING]) ?>" class="nav-link <?= $action == 'inbox' && $searchModel->status == Tickets::STATUS_DOING ? 'active' : '' ?>">
                    <?= Yii::t("app", "Ticket Doing") ?>
                    <?php if ($doing_count > 0) : ?>
                        <span class="badge badge-danger pull-left"><?= $doing_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= Url::to(['inbox', 'TicketsSearch[status]' => Tickets::STATUS_CLOSE]) ?>" class="nav-link <?= $action == 'inbox' && $searchModel->status == Tickets::STATUS_CLOSE ? 'active' : '' ?>">
                    <?= Yii::t("app", "Ticket Closed") ?>
                </a>
            </li>
        </ul>
    </div>
    <div class="d-flex flex-column flex-grow-1">
        <ul class="nav nav-tabs nav-fill bg-white pt-3">
            <li class="nav-item">
                <a href="<?= Url::to(['outbox', 'TicketsSearch[status]' => Tickets::STATUS_ACTIVE]) ?>" class="nav-link <?= $action == 'outbox'  ? 'active' : '' ?>">
                    <i class="fa fa-upload"></i>
                    <?= Yii::t("app", "Outbox") ?>
                </a>
            </li>
        </ul>
        <ul class="nav nav-tabs nav-fill bg-white mb-3 <?= $action == 'inbox'  ? 'd-none' : '' ?>">
            <li class="nav-item">
                <a href="<?= Url::to(['outbox', 'TicketsSearch[status]' => Tickets::STATUS_ACTIVE]) ?>" class="nav-link <?= $action == 'outbox' && $searchModel->status == Tickets::STATUS_ACTIVE ? 'active' : '' ?>">
                    <?= Yii::t("app", "Active") ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= Url::to(['outbox', 'TicketsSearch[status]' => Tickets::STATUS_DOING]) ?>" class="nav-link <?= $action == 'outbox' && $searchModel->status == Tickets::STATUS_DOING ? 'active' : '' ?>">
                    <?= Yii::t("app", "Ticket Doing") ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= Url::to(['outbox', 'TicketsSearch[status]' => Tickets::STATUS_CLOSE]) ?>" class="nav-link <?= $action == 'outbox' && $searchModel->status == Tickets::STATUS_CLOSE ? 'active' : '' ?>">
                    <?= Yii::t("app", "Ticket Closed") ?>
                </a>
            </li>
        </ul>
    </div>
</div>