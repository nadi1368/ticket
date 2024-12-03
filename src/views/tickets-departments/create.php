<?php


/* @var $this yii\web\View */
/* @var $model \hesabro\ticket\models\TicketsDepartments */

$this->title = Yii::t('tickets', 'Create Tickets Departments');
$this->params['breadcrumbs'][] = ['label' => Yii::t('tickets', 'Tickets Departments'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tickets-departments-create card">
	<?= $this->render('_form', [
		'model' => $model,
	]) ?>
</div>
