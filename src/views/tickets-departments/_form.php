<?php

use hesabro\ticket\models\Tickets;
use kartik\select2\Select2;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \hesabro\ticket\models\TicketsDepartments */
/* @var $form yii\bootstrap4\ActiveForm */
?>

<div class="tickets-departments-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-tickets-departments',
        'options' => ['data-pjax' => true,]
    ]); ?>
    <div class="card-body">
        <div class="row">

            <div class="col-md-6">
                <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
            </div>

            <div class="col-md-12">
                <?= $form->field($model, 'user_ids')->widget(Select2::class, [
                    'data' => Tickets::itemAlias('Owner'),
                    'options' => [
                        'placeholder' => 'کاربران',
                        'dir' => 'rtl',
                        'multiple' => true,
                    ],
                ]); ?>
            </div>

        </div>
    </div>
    <div class="card-footer">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('tickets', 'Create') : Yii::t('tickets', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
