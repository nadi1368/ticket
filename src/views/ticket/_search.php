<?php

use hesabro\ticket\models\Tickets;
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
        'action' => [Yii::$app->controller->action->id],
        'method' => 'get',
    ]); ?>

    <div class="row">

        <div class="col-md-2">
            <?= $form->field($model, 'status')->dropDownList(Tickets::itemAlias('Status'), ['prompt' => Yii::t('app', 'Select...')]) ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($model, 'priority')->dropDownList(Tickets::itemAlias('Priority'), ['prompt' => Yii::t('app', 'Select...')]) ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($model, 'class_name')
                ->dropDownList(Tickets::itemAlias('ClassNameFilter'), ['prompt' => Yii::t('app', 'Select...')]) ?>
        </div>

        <div class="col-md-4">
            <?= $form->field($model, 'owner')->widget(Select2::class, [
                'data' => Tickets::itemAlias('Owner'),
                'options' => [
                    'placeholder' => 'کاربران',
                    'dir' => 'rtl',
                    'multiple' => true,
                ],
            ]); ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'due_date')->widget(MaskedInput::class, [
                'mask' => '9999/99/99',
            ]) ?>
        </div>

        <div class="col-12 align-self-center text-right">
            <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        </div>

    </div>

    <?php ActiveForm::end(); ?>

</div>