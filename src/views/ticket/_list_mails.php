<?php
use hesabro\ticket\models\Tickets;
use hesabro\ticket\TicketModule;

?>

<div class='min-width-340'>
    <div class='border-end user-chat-box h-100'>
        <div class='px-4 pt-9 pb-6 d-none d-lg-block'>
            <form class='position-relative'>
                <input type='text' class='form-control search-chat py-2 ps-5' id='text-srh'
                       placeholder='Search'/>
                <i class='ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3'></i>
            </form>
        </div>
        <div class='app-chat'>
            <ul class='chat-users mh-n100' data-simplebar>
                <?php foreach ($dataProvider->getModels() as $model): /** @var $model Tickets */ ?>
                    <li>
                        <a href='javascript:void(0)'
                           data-mail_id='<?= $model->id ?>'
                           class='mail-view-link px-4 py-3 bg-hover-light-black d-flex align-items-start chat-user <?= !$model->getViewed() ? 'bg-light-subtle' : '' ?>'>
                            <div class='form-check mb-0'>
                                <input class='form-check-input' type='checkbox' value=''
                                       id='flexCheckDefault'/>
                            </div>
                            <div class='position-relative w-100 ms-2'>
                                <div class='d-flex align-items-center justify-content-between mb-2'>
                                    <h6 class='mb-0 <?= $model->getViewed() ? 'fw-light text-muted' : 'fw-semibold text-dark' ?>'><?= $model->title ?></h6>
                                    <?php if ($model->priority): ?>
                                        <span class='badge text-bg-<?= Tickets::itemAlias('PriorityClass', $model->priority) ?>'><?= Tickets::itemAlias('Priority', $model->priority) ?></span>
                                    <?php endif; ?>
                                </div>
                                <h6 class='<?= $model->getViewed() ? 'fw-light text-muted' : 'fw-semibold text-dark' ?>'>
                                    <?= substr($model->des, 0, 50) . (strlen($model->des) > 50 ? '....' : ''); ?>
                                </h6>
                                <div class='d-flex align-items-center justify-content-between'>
                                    <div class='d-flex align-items-center'>
                                  <span>
                                    <i class='ti ti-star fs-4 me-2 text-dark'></i>
                                  </span>
                                        <span class='d-block'>
                                    <i class='ti ti-alert-circle text-muted'></i>
                                  </span>
                                    </div>
                                    <p class='mb-0 fs-2 text-muted'><?= Yii::$app->jdate->date('Y/m/d H:i', $model->created) . ' - ' . $model->getCreatorFullName() ?></p>
                                </div>
                                <?php if ($model->status == Tickets::STATUS_DOING): ?>
                                    <span class='badge text-bg-info'><?= Tickets::itemAlias('Status', $model->status) . ' توسط ' . $model->assignedTo?->fullName ?></span>
                                <?php endif; ?>
                                <?php if ($model->type == Tickets::TYPE_MASTER && TicketModule::getInstance()->hasSlaves && Yii::$app->client->isMaster()): ?>
                                    <span class='badge text-bg-info'><?= \backend\modules\master\models\Client::findOne($model->slave_id)->title ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
