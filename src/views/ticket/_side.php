<?php

use hesabro\ticket\models\Tickets;
use yii\helpers\Html;

/* @var yii\web\View $this */
/* @var $searchModel hesabro\ticket\models\TicketsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$links = [
    [
        'title' => Yii::t('tickets', 'Inbox'),
        'link' => ['index'],
        'active' => $searchModel->type == 'inbox' && !$searchModel->status,
        'count' => Tickets::countInbox()
    ],
    [
        'title' => Yii::t('tickets', 'Doing'),
        'link' => ['index', 'TicketsSearch' => ['status' => Tickets::STATUS_DOING]],
        'active' => $searchModel->status == Tickets::STATUS_DOING,
        'count' => Tickets::countInbox(Tickets::STATUS_DOING)
    ],
    [
        'title' => Yii::t('tickets', 'Outbox'),
        'link' => ['index', 'outbox' => 1],
        'active' => $searchModel->type == 'outbox',
        'count' => 0
    ],
    [
        'title' => Yii::t('tickets', 'Closed'),
        'link' => ['index', 'TicketsSearch' => ['status' => Tickets::STATUS_CLOSE]],
        'active' => $searchModel->status == Tickets::STATUS_CLOSE,
        'count' => 0
    ],
];

?>

<div class='left-part border-end w-20 flex-shrink-0 d-none d-lg-block h-auto'>
    <div class='px-9 pt-4 pb-3 row'>
        <div class="col-md-6">
            <?= Html::button($t = Yii::t('tickets', 'New Ticket'), [
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
        <div class='col-md-6'>
            <?= Html::button($t = Yii::t('tickets', 'New Support Ticket Ticket'), [
                'class' => 'btn btn-primary fw-semibold py-8 w-100',
                'title' => $t,
                'data-title' => $t,
                'data-size' => 'modal-xl',
                'data-toggle' => 'modal',
                'data-target' => '#modal-pjax',
                'data-url' => \yii\helpers\Url::to(['ticket/send', 'master' => 1]),
                'data-reload-pjax-container' => 'mail_box',
            ]) ?>
        </div>
    </div>
    <ul class='list-group mh-n100' data-simplebar>
        <?php foreach ($links as $link): ?>
            <li class='list-group-item border-0 p-0 mx-9 <?= $link['active'] ? 'bg-info rounded' : '' ?>'>
                <a class='d-flex align-items-center gap-6 list-group-item-action px-3 py-8 mb-1 rounded-1 <?= $link['active'] ? 'text-white' : '' ?>'
                   data-pjax="0"
                   href='<?= \yii\helpers\Url::to($link['link']) ?>'>
                    <i class='fal fa-email'></i><?= $link['title'] ?>
                    <?php if ($link['count'] ?? null): ?>
                        <span class='badge text-bg-warning'><?= $link['count'] ?></span>
                    <?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>
        <li class='border-bottom my-3'></li>
        <li class='fw-semibold text-dark text-uppercase mx-9 my-2 px-3 fs-2'>
            <h5><?= Yii::t('tickets', 'Search') ?></h5>
        </li>
        <?= $this->render('_search', ['model' => $searchModel]) ?>
    </ul>
</div>
