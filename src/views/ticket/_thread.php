<?php

use hesabro\ticket\models\Comments;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var yii\web\View $this */
/* @var Comments $thread */

$css = <<< CSS
.history-card {
    text-align: center;
    background-color: #e7e7e7;
    padding: 14px;
    border-radius: 8px;
    margin-bottom: 30px;
    gap: 4px;
    font-size: 12px;
    color: #858585;
}
CSS;
$this->registerCss($css);

$messages = $thread?->getMessages()->all() ?: [];
$rootTicket = current(array_filter($messages, fn(Comments $comment) => !$comment->parent_id)) ?: null;
?>

<div class="card">
    <?php if ($title = $rootTicket?->title): ?>
        <div class="px-3 pt-4">
            <h4><?= $title ?></h4>
        </div>
    <?php endif; ?>

    <?php if ($thread): ?>
        <div class="px-3 py-4">
            <?= $this->render('@hesabro/ticket/views/ticket/_overview', ['model' => $thread]) ?>
        </div>
        <hr class="m-0">
    <?php endif; ?>

    <?php Pjax::begin([
        'id' => 'messages-container',
        'enablePushState' => false,
        'enableReplaceState' => false,
        'linkSelector' => false
    ]); ?>

    <?php if (count($messages)): ?>
        <div class="p-3" style="overflow-y: auto; height: calc(100vh - 475px)">
            <?php
            /** @var Comments $message */
            foreach ($messages as $message): ?>
                <?php if ($message->type_task): ?>
                    <div class="row justify-content-center">
                        <div class="col-4 history-card d-flex flex-column">
                            <span><?= $message->title ?></span>
                            <span><?= $message->des ?></span>
                            <small class="text-center"><?= Yii::$app->jdate->date('H:i Y/m/d', $message->created) ?></small>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (!$message->parent_id && $message->creator): ?>
                    <div class="row justify-content-center">
                        <div class="col-4 history-card d-flex flex-column">
                            <span>این تیکت توسط <?= $message->creator?->fullName ?> ایجاد شد.</span>
                            <small class="text-center"><?= Yii::$app->jdate->date('H:i Y/m/d', $message->created) ?></small>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($message->kind === Comments::KIND_REFER): ?>
                    <div class="row justify-content-center">
                        <div class="col-4 history-card d-flex flex-column">
                            <span>این تیکت توسط <?= $message->creator?->fullName ?> به <?= implode('، ', array_map(fn(User $user) => $user->fullName, $message->users)) ?> ارجاع داده شد.</span>
                            <small class="text-center"><?= Yii::$app->jdate->date('H:i Y/m/d', $message->created) ?></small>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($message->des && !((int) $message->type_task)): ?>
                    <div class="row no-gutters <?= !$message->viewerIsAuthor ? 'justify-content-end' : '' ?>">
                        <div class="col-md-6">
                            <div class="card shadow-sm border">
                                <div class="card-body p-3">
                                    <?php if (!$message->viewerIsAuthor) : ?>
                                        <p><strong><?= $message->creator_id === 0 ? Yii::t('app', 'System') : $message->creator?->fullName ?></strong> <span
                                                    class="font-light"><?= $message->creator?->job ?></span></p>
                                    <?php endif; ?>
                                    <p class="font-normal"><?= nl2br($message->des) ?></p>

                                    <?= ($fileUrl = $message->getFileUrl('file')) ? Html::a('<span class="badge badge-info">دانلود فایل پیوست</span>', $fileUrl, ['data-pjax' => 0]) . '<br>' : '' ?>

                                    <p class="m-0 d-flex justify-content-end">
                                        <small><?= Yii::$app->jdate->date('H:i Y/m/d', $message->created) ?></small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            <div id="messages-end"></div>
        </div>
    <?php else: ?>
        <div class="d-flex align-items-center justify-content-center" style="min-height: 250px">
            <p class="mb-0"><?= Yii::t('app', 'No Work Cycle Found') ?></p>
        </div>
    <?php endif; ?>

    <?php Pjax::end() ?>

    <?php if ($thread): ?>
        <hr class="m-0"/>

        <?= $this->render('_reply', [
            'thread' => $thread,
            'model' => new Comments([
                'scenario' => Comments::SCENARIO_CREATE
            ])
        ]) ?>
    <?php endif; ?>
</div>
