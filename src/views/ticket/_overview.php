<?php

use hesabro\ticket\models\Tickets;
use hesabro\ticket\models\TicketsSearch;
use hesabro\ticket\TicketModule;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model Tickets */
/* @var $searchModel TicketsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controller = Yii::$app->controller->id;
$action = Yii::$app->controller->action->id;
$fullDetail = in_array("$controller/$action", [
    'ticket/view'
]);
$canChangeStatus = $model->canChangeStatus()
?>

<div class="row">
    <div class="col-md-4">
        <div class="compose-btn">
            <?= $fullDetail ? Html::a(
                '<i class="fa fa-reply"></i> ' . Yii::t("tickets", "Answer"),
                [
                    'ticket/send',
                    'owner' => $model->creator_id,
                    'parent_id' => $model->id,
                    'title' => 'پاسخ به - ' . $model->title,
                ],
                [
                    'title' => Yii::t("tickets", "Answer"),
                    'class' => 'btn btn-sm btn-primary showModalButton'
                ]
            ) : '' ?>
            <?= $canChangeStatus && ($model->status == Tickets::STATUS_ACTIVE || $model->status == Tickets::STATUS_DOING) ?
                Html::a(
                    '<i class="fa fa-times"></i> ' . Yii::t("tickets", "Close Ticket"),
                    'javascript:void(0)',
                    [
                        'title' => Yii::t("tickets", "Close Ticket"),
                        'aria-label' => Yii::t("tickets", "Close Ticket"),
                        'data-reload-pjax-container' => 'mail_box',
                        'data-pjax' => '0',
                        'data-url' => Url::to([
                            'ticket/change-status',
                            'type' => Tickets::STATUS_CLOSE,
                            'id' => $model->id,
                        ]),
                        'class' => "btn btn-sm btn-danger p-jax-btn",
                        'data-title' => Yii::t("tickets", "Close Ticket"),
                        'data-method' => 'post',
                        'data-confirm-alert' => 1,
                        'data-confirm-title' => Yii::t("tickets", "Close Ticket"),
                    ]
                ) : '';
            ?>
            <?= $canChangeStatus && $model->status == Tickets::STATUS_ACTIVE ?
                Html::a(
                    '<i class="fa fa-thumbs-up"></i> ' . Yii::t("tickets", "Change To Doing"),
                    'javascript:void(0)',
                    [
                        'title' => Yii::t("tickets", "Change To Doing"),
                        'aria-label' => Yii::t("tickets", "Change To Doing"),
                        'data-reload-pjax-container' => 'mail_box',
                        'data-pjax' => '0',
                        'data-url' => Url::to([
                            'ticket/change-status',
                            'type' => Tickets::STATUS_DOING,
                            'id' => $model->id,
                        ]),
                        'class' => "btn btn-sm btn-info p-jax-btn",
                        'data-title' => Yii::t("tickets", "Change To Doing"),
                        'data-method' => 'post',
                        'data-confirm-alert' => 1,
                        'data-confirm-title' => Yii::t("app", "Change To Doing"),
                    ]
                ) : '';
            ?>
            <?= $canChangeStatus && $model->status == Tickets::STATUS_CLOSE ?
                Html::a(
                    '<i class="fa fa-check"></i> ' . Yii::t("tickets", "Open Ticket"),
                    'javascript:void(0)',
                    [
                        'title' => Yii::t("tickets", "Open Ticket"),
                        'aria-label' => Yii::t("tickets", "Open Ticket"),
                        'data-reload-pjax-container' => 'mail_box',
                        'data-pjax' => '0',
                        'data-url' => Url::to([
                            'ticket/change-status',
                            'type' => Tickets::STATUS_ACTIVE,
                            'id' => $model->id,
                        ]),
                        'class' => "btn btn-sm btn-success p-jax-btn",
                        'data-title' => Yii::t("tickets", "Open Ticket"),
                        'data-method' => 'post',
                        'data-confirm-alert' => 1,
                        'data-confirm-title' => Yii::t("tickets", "Open Ticket"),
                    ]
                ) : '';
            ?>
            <?= $canChangeStatus && ($model->status == Tickets::STATUS_ACTIVE || $model->status == Tickets::STATUS_DOING) ?
                Html::a('<i class="fas fa-directions"></i> ' . Yii::t("tickets", 'Refer'),
                    'javascript:void(0)', [
                        'title' => Yii::t("tickets", 'Refer Ticket'),
                        'aria-label' => Yii::t("tickets", 'Refer Ticket'),
                        'data-pjax' => '0',
                        'data-url' => Url::to([
                            'ticket/refer',
                            'id' => $model->id,
                        ]),
                        'class' => 'btn btn-warning btn-sm',
                        'id' => 'refer-ticket',
                        'data-size' => 'modal-md',
                        'data-title' => Yii::t('tickets', 'Refer Ticket'),
                        'data-toggle' => 'modal',
                        'data-target' => '#modal-pjax-over',
                        'data-reload-pjax-container-on-show' => 0,
                        'data-reload-pjax-container' => 'mail_box',
                        'data-handleFormSubmit' => 1,
                        'disabled' => true
                    ]) : '' ?>
        </div>
    </div>
    <div class="col-md-6 d-flex align-items-center justify-content-end" style="gap: 8px">
        <p class="date mb-0"> <?= Yii::$app->jdate->date('Y/m/d H:i', $model->created) ?></p>
        <?php if ($fullDetail) : ?>
            <div class="compose-btn">
                <?= Html::a(
                    '<i class="fa fa-comments-alt"></i> ' . Yii::t('tickets', 'History') . ' و ' . Yii::t('tickets', 'Thread'),
                    Url::to(['ticket/thread', 'id' => $model->parent_id && $model->kind === Tickets::KIND_THREAD ? $model->parent_id : $model->id]),
                    [
                        'class' => "btn btn-sm btn-linkedin",
                    ]
                );
                ?>
            </div>
        <?php endif; ?>
        <h4 class="mb-0">
            <?= Yii::t("tickets", "Sender") . ':' ?>
            <span class="badge badge-inverse px-2 py-1 d-inline-flex">
                <?= $model->creator_id === 0 ? Yii::t('tickets', 'System') : $model->getCreatorFullName() ?>
            </span>
            <?php if ($model->type == Tickets::TYPE_MASTER && TicketModule::getInstance()->hasSlaves && Yii::$app->client->isMaster()): ?>
                <span class="badge badge-info px-2 py-1 d-inline-flex">
                    <?= \backend\modules\master\models\Client::findOne($model->slave_id)->title ?>
                </span>
            <?php endif; ?>
        </h4>
    </div>
    <hr class="mt-3">
    <div class='col-md-7 mt-1'>
        <h4 class='mb-0'>
            <?= Yii::t('tickets', 'Department') . ':' ?>
            <span class="badge badge-info px-2 py-1 d-inline-flex">
                <?= $model->getDepartmentTitle() ?: Yii::t('tickets', 'No Department') ?>
            </span>
        </h4>
    </div>
    <?php if ($fullDetail) : ?>
        <div class="col-md-12">
            <hr />
        </div>
        <div class="col-md-12">
            <div class="view-mail text-justify">
                <?= ($fileUrl = $model->getFileUrl('file')) ? Html::a('<span class="badge badge-info">دانلود فایل پیوست</span>', $fileUrl, ['data-pjax' => 0]) . '<br>' : '' ?>
                <?= nl2br($model->des) ?>
                <?= $model->link ? '<hr/>' . Html::a('مشاهده ' . $model->title, str_starts_with($model->link, 'http') ? $model->link : [$model->link, 'id' => $model->class_id]) : '' ?>
            </div>
        </div>
    <?php endif; ?>
</div>