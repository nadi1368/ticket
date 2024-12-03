<?php

use hesabro\ticket\models\Tickets;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $thread Tickets */
/* @var $tickets yii\data\ActiveDataProvider */

$this->title = $thread->title;
$this->params['breadcrumbs'][] = $thread->title;

$css = <<< CSS
.line-clamp-1 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
}
CSS;
$this->registerCss($css);

Pjax::begin(['id' => 'mail_box']);
?>
<div class="d-flex flex-column position-absolute w-100 h-100" style="left: 0; top: 0;">

    <div class="flex-grow-1 row p-5">

        <div class="col-md-8">
            <?= $this->renderFile('@hesabro/ticket/views/ticket/_thread.php', [
                'thread' => $thread
            ]) ?>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><?= Yii::t("app", 'Threads') ?></div>
                <div class="card-body px-0 py-2" style="overflow-y: auto;max-height: calc(100vh - 300px);"
                     id="side-threads">
                    <?php foreach ($tickets->getModels() as $ticket) : ?>
                        <div class="border-bottom py-2 <?= $ticket->id == Yii::$app->request->get('id', '') ? 'bg-light' : '' ?>">
                            <?= Html::a(
                                '<span><strong>' . $ticket->title . '</strong><br/><small class="line-clamp-1">' . $ticket->latestMessage?->des . '</small></span>',
                                Url::to(['ticket/thread', 'id' => $ticket->id]),
                                ['class' => 'd-block px-3', 'data-pjax' => 0]
                            ) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
Pjax::end();