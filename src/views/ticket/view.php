<?php

use hesabro\ticket\models\Comments;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model Comments */
/* @var $searchModel backend\models\CommentsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $model->title;
$this->params['breadcrumbs'][] = $model->title;
?>

<div class="incard-body">
    <?= $this->render('_overview', ['model' => $model]) ?>
</div>