<?php

/* @var $this yii\web\View */
/* @var $model \hesabro\ticket\models\TicketsDepartments */

$this->title = Yii::t('tickets', 'Update');
$this->params['breadcrumbs'][] = ['label' => Yii::t('tickets', 'Tickets Departments'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('tickets', 'Update');
?>
<div class="tickets-departments-update card">
	<?= $this->render('_form', [
		'model' => $model,
	]) ?>
</div>
