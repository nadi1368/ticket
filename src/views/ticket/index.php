<?php

use hesabro\helpers\widgets\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Comments');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="comments-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Comments'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'creator_id',
            'update_id',
            'owner',
            'class_name',
            //'class_id',
            //'des:ntext',
            //'css_class',
            //'status',
            //'due_date',
            //'created',
            //'changed',

            ['class' => 'common\widgets\grid\ActionColumn'],
        ],
    ]); ?>
</div>
