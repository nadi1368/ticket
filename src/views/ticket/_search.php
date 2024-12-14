<?php

use hesabro\ticket\models\Tickets;
use hesabro\ticket\models\TicketsDepartments;
use hesabro\ticket\models\TicketsSearch;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;

/* @var $this yii\web\View */
/* @var $model TicketsSearch */
/* @var $form yii\bootstrap4\ActiveForm */
?>

<div class="card-body">

    <?php $form = ActiveForm::begin([
        //'action' => [Yii::$app->controller->action->id],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'department_id')->dropdownList(TicketsDepartments::itemAlias('List'), [
                'prompt' => Yii::t('tickets', 'Select...'),
                'allowClear' => true
            ]); ?>
        </div>

        <?php if($model->status == Tickets::STATUS_DOING): ?>
            <?= $form->field($model, 'assigned_to')->widget(Select2::class, [
                'data' => Tickets::itemAlias('Owner'),
                'options' => [
                    'placeholder' => 'کاربران',
                    'dir' => 'rtl',
                ],
            ]); ?>
        <?php endif; ?>

        <div class="col-12 align-self-center text-right">
            <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary fw-semibold py-8 w-100']) ?>
        </div>

    </div>

    <?php ActiveForm::end(); ?>

</div>