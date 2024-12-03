<?php

use hesabro\ticket\models\Tickets;

/* @var $this yii\web\View */
/* @var $model Tickets */
/* @var $searchModel backend\models\CommentsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $model->title;
$this->params['breadcrumbs'][] = $model->title;
?>

<div class="incard-body">
    <?= $this->render('_overview', ['model' => $model]) ?>
</div>