<?php

use hesabro\ticket\models\Comments;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;

/* @var $this yii\web\View */
/* @var $model backend\models\CommentsSearch */
/* @var $form yii\bootstrap4\ActiveForm */
?>

<div class="card-body">

    <?php $form = ActiveForm::begin([
        'action' => [Yii::$app->controller->action->id],
        'method' => 'get',
    ]); ?>

    <div class="row">

        <div class="col-md-2">
            <?= $form->field($model, 'status')->dropDownList(Comments::itemAlias('Status'), ['prompt' => Yii::t('app', 'Select...')]) ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($model, 'css_class')->dropDownList(Comments::itemAlias('Type'), ['prompt' => Yii::t('app', 'Select...')]) ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($model, 'class_name')
                ->dropDownList(Comments::itemAlias('ClassNameFilter'), ['prompt' => Yii::t('app', 'Select...')]) ?>
        </div>

        <div class="col-md-4">
            <?php if ($model->type == Comments::TYPE_MASTER) : ?>
                <?= $form->field($model, 'master_task_type_id')->dropDownList(Comments::itemAlias('MasterTaskType'), ['prompt' => Yii::t('app', 'Select...')]) ?>
            <?php else : ?>
                <?= $form->field($model, 'owner')->widget(Select2::class, [
                    'data' => Comments::itemAlias('Owner'),
                    'options' => [
                        'placeholder' => 'کاربران',
                        'dir' => 'rtl',
                        'multiple' => true,
                    ],
                ]); ?>
            <?php endif; ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'due_date')->widget(MaskedInput::class, [
                'mask' => '9999/99/99',
            ]) ?>
        </div>

        <?php // echo $form->field($model, 'class_id') 
        ?>

        <?php // echo $form->field($model, 'des') 
        ?>

        <?php // echo $form->field($model, 'css_class') 
        ?>

        <?php // echo $form->field($model, 'status') 
        ?>

        <?php // echo $form->field($model, 'due_date') 
        ?>

        <?php // echo $form->field($model, 'created') 
        ?>

        <?php // echo $form->field($model, 'changed') 
        ?>

        <div class="col-12 align-self-center text-right">
            <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        </div>

    </div>

    <?php ActiveForm::end(); ?>

</div>