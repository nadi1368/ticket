<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model \hesabro\ticket\models\TicketsDepartments */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('tickets', 'Tickets Departments'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tickets-departments-view card">
	<div class="card-body">
	<?= DetailView::widget([
		'model' => $model,
		'attributes' => [
            'id',
            'title',
            'status',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
            'slave_id',
		],
	]) ?>
	</div>
	<div class="card-footer">
		<?php // Html::a(Yii::t('tickets', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
		<?= Html::a(Yii::t('tickets', 'Delete'), ['delete', 'id' => $model->id], [
		'class' => 'btn btn-danger',
		'data' => [
		'confirm' => Yii::t('tickets', 'Are you sure you want to delete this item?'),
		'method' => 'post',
		],
		]) ?>
	</div>
</div>
