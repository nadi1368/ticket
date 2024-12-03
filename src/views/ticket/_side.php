<?php

use hesabro\ticket\models\Tickets;
use yii\helpers\Html;

$links = [
    [
        'title' => Yii::t('tickets', 'Inbox'),
        'link' => array_merge(['index']),
        'active' => false,
        'count' => Tickets::countInbox()
    ],
    [
        'title' => Yii::t('tickets', 'Doing'),
        'link' => array_merge(['index', 'TicketsSearch' => ['status' => Tickets::STATUS_DOING]]),
        'active' => false,
        'count' => Tickets::countInbox(Tickets::STATUS_DOING)
    ],
    [
        'title' => Yii::t('tickets', 'Outbox'),
        'link' => array_merge(['index', 'outbox' => 1]),
        'active' => false,
        'count' => 0
    ],
];

?>

<div class='left-part border-end w-20 flex-shrink-0 d-none d-lg-block h-auto'>
    <div class='px-9 pt-4 pb-3'>
        <?= Html::button($t = Yii::t('app', 'New Ticket'), [
            'class' => 'btn btn-primary fw-semibold py-8 w-100',
            'title' => $t,
            'data-title' => $t,
            'data-size' => 'modal-xl',
            'data-toggle' => 'modal',
            'data-target' => '#modal-pjax',
            'data-url' => \yii\helpers\Url::to(['ticket/send']),
            'data-reload-pjax-container' => 'mail_box',
        ]) ?>
    </div>
    <ul class='list-group mh-n100' data-simplebar>
        <?php foreach ($links as $link): ?>
            <li class='list-group-item border-0 p-0 mx-9 <?= $link['active'] ? 'active' : '' ?>'>
                <a class='d-flex align-items-center gap-6 list-group-item-action text-dark px-3 py-8 mb-1 rounded-1'
                   data-pjax="0"
                   href='<?= \yii\helpers\Url::to($link['link']) ?>'>
                    <i class='fal fa-email'></i><?= $link['title'] ?>
                    <?php if ($link['count'] ?? null): ?>
                        <span class='badge text-bg-warning'><?= $link['count'] ?></span>
                    <?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
