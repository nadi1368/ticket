<?php

use hesabro\ticket\models\Comments;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $children Comments[] */
?>
<div class="row">
    <?php foreach ($children as $model): ?>

        <?php if ($model->creator_id == Yii::$app->user->id): ?>
            <div class="col-md-12">
                <hr style="border: 1px solid black"/>
            </div>
            <div class="col-md-2">
                <p class="text-info"><?= $model->sender ?></p>
                <p class="date text-left"> <?= Yii::$app->jdate->date('Y/m/d H:i', $model->created) ?></p>
            </div>
            <div class="col-md-10">
                <div class="view-mail text-justify">
                    <?= $model->des ?>
                    <?= '<hr/>' . $model->htmlLink ?>
                </div>
            </div>
        <?php else: ?>
            <div class="col-md-12">
                <hr style="border: 1px solid black"/>
            </div>
            <div class="col-md-2">
                <p class="text-info text-left"><?= $model->sender ?></p>
                <p class="date text-left"> <?= Yii::$app->jdate->date('Y/m/d H:i', $model->created) ?></p>
            </div>
            <div class="col-md-10">
                <div class="view-mail text-justify">
                    <?= $model->des ?>
                    <?= '<hr/>' . $model->htmlLink ?>
                </div>
            </div>

        <?php endif; ?>

    <?php endforeach; ?>
</div>