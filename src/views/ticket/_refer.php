<?php

use hesabro\ticket\models\Comments;
use kartik\select2\Select2;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\MaskedInput;

/**
 * @var yii\web\View $this
 * @var Comments $model
 * @var Comments $comment
 * @var yii\bootstrap4\ActiveForm $form
 */
?>

<div class="comments-form">
    <?php $form = ActiveForm::begin([
        'action' => Url::to(['ticket/refer', 'id' => $comment->id]),
        'id' => 'ticket-refer-form'
    ]); ?>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'owner')->widget(Select2::class, [
                        'data' => Comments::itemAlias('Owner'),
                        'options' => [
                            'placeholder' => 'کاربران',
                            'dir' => 'rtl',
                            'multiple' => true,
                        ],
                    ]); ?>
                </div>

                <div class="col-12 col-md-6">
                    <?= $form->field($model, 'send_email', ['options' => ['class' => 'mb-2']])->checkbox(['id' => 'email-checkbox']) ?>
                    <?= $form->field($model, 'send_email_at')->widget(MaskedInput::class, [
                        'mask' => '9999/99/99 99:99',
                        'options' => [
                            'id' => 'email-date',
                            'placeholder' => 'تاریخ ارسال ایمیل (اختیاری)',
                            'disabled' => !$model->send_email
                        ]
                    ])->label(false)->hint('در صورت تنظیم نشدن تاریخ، بعد از ذخیره ایمیل ارسال می‌شود.') ?>
                </div>

                <div class="col-12 col-md-6">
                    <?= $form->field($model, 'send_sms', ['options' => ['class' => 'mb-2']])->checkbox(['id' => 'sms-checkbox']) ?>
                    <div class="date-input">
                        <?= $form->field($model, 'send_sms_at')->widget(MaskedInput::class, [
                            'mask' => '9999/99/99 99:99',
                            'options' => [
                                'id' => 'sms-date',
                                'placeholder' => 'تاریخ ارسال پیامک (اختیاری)',
                                'disabled' => !$model->send_sms
                            ],
                        ])->label(false)->hint('در صورت تنظیم نشدن تاریخ، بعد از ذخیره پیامک ارسال می‌شود.') ?>
                    </div>
                </div>

                <div class="col-md-12">
                    <?= $form->field($model, 'des')->textarea(['rows' => 3]) ?>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <?= Html::submitButton(Yii::t('app', 'Refer'), ['class' => 'btn btn-success ']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php
$js = <<<JS
$(document).ready(() => {
    $('#sms-checkbox').on('change', function () {
        console.log('change')
        const input = $('#sms-date')
        if ($(this).is(':checked')) {
            input.attr('disabled', false)
        } else {
            input.attr('disabled', true)
            input.val('')
        }
    })
    
    $('#email-checkbox').on('change', function () {
        const input = $('#email-date')
        if ($(this).is(':checked')) {
            input.attr('disabled', false)
        } else {
            input.attr('disabled', true)
            input.val('')
        }
    })
    
    $('input[name="due_date"]').daterangepicker("#date-RemoveBtn", {
        "locale": {"format": "jYYYY/jMM/jDD"},
        "drops": "down",
        "opens": "right",
        "jalaali": true,
        "showDropdowns": true,
        "language": "fa",
        "singleDatePicker": true,
        "useTimestamp": true,
        "timePicker": false,
        "timePickerSeconds": true,
        "timePicker24Hour": true
    })
            
    $('#email-date').daterangepicker("#date-RemoveBtn", {
        "locale": {"format": "jYYYY/jMM/jDD HH:mm"},
        "drops": "down",
        "opens": "right",
        "jalaali": true,
        "showDropdowns": true,
        "language": "fa",
        "singleDatePicker": true,
        "useTimestamp": true,
        "timePicker": true,
        "timePickerSeconds": false,
        "timePicker24Hour": true
    })
    
    $('#sms-date').daterangepicker("#date-RemoveBtn", {
        "locale": {"format": "jYYYY/jMM/jDD HH:mm"},
        "drops": "down",
        "opens": "right",
        "jalaali": true,
        "showDropdowns": true,
        "language": "fa",
        "singleDatePicker": true,
        "useTimestamp": true,
        "timePicker": true,
        "timePickerSeconds": false,
        "timePicker24Hour": true
    })
})
JS;

$this->registerJs($js);
?>
