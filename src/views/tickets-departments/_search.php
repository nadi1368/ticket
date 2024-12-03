<?php

use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \hesabro\ticket\models\TicketsDepartmentsSearch */
/* @var $form yii\bootstrap4\ActiveForm */
?>

<div class="tickets-departments-search">

    <?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
            'options' => [
        'data-pjax' => 1
        ],
        ]); ?>
    <div class="card-body">
        <div class="row">
                    <div class="col-md-2">
        <?= $form->field($model, 'id') ?>
        </div>

        <div class="col-md-2">
        <?= $form->field($model, 'title') ?>
        </div>

        <div class="col-md-2">
        <?= $form->field($model, 'status') ?>
        </div>

        <div class="col-md-2">
        <?= $form->field($model, 'created_at') ?>
        </div>

        <div class="col-md-2">
        <?= $form->field($model, 'created_by') ?>
        </div>

        <div class="col-md-2">
        <?php // echo $form->field($model, 'updated_at') ?>
        </div>

        <div class="col-md-2">
        <?php // echo $form->field($model, 'updated_by') ?>
        </div>

        <div class="col-md-2">
        <?php // echo $form->field($model, 'slave_id') ?>
        </div>

            <div class="col align-self-center text-right">
                <?= Html::submitButton(Yii::t('tickets', 'Search'), ['class' => 'btn btn-primary']) ?>
                <?= Html::resetButton(Yii::t('tickets', 'Reset'), ['class' => 'btn btn-secondary']) ?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
