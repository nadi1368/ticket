<?php

use hesabro\ticket\models\Tickets;
use hesabro\ticket\models\TicketsDepartments;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use yii\widgets\MaskedInput;
use kartik\select2\Select2;

/** @var yii\web\View $this */
/** @var common\models\Comments $model */
/** @var yii\bootstrap4\ActiveForm $form */
/* @var $owner false|mixed */
/* @var $parent_id int|mixed */

$styles = <<<CSS
	.field-comments-file label {
		border: 1px dashed #ddd;
		border-radius: 2px;
		padding: 8px;
		min-height: 100px;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;

		transition: border 0.33s ease-in-out;
	}

	.field-comments-file label:hover {
		border-style: solid;
		cursor: pointer;
	}
CSS;

$this->registerCss($styles);

?>

<div class="comments-form">

    <?php $form = ActiveForm::begin([
        'id' => 'ajax-form-comment-answer',
        'options' => [
            'enctype' => "multipart/form-data",
        ],
    ]); ?>
    <div class="card-body">
        <?php if ($model->type == Tickets::TYPE_MASTER): ?>
            <div class="alert alert-warning mb-4 text-center">
                <h4 class="m-0">شما از طریق این فرم می‌توانید با تیم پشتیبانی حسابرو در ارتباط باشید و درخواست‌های خودرا
                    مطرح نمایید.</h4>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class='col-md-6'>
                <?= $form->field($model, 'department_id')->dropdownList($model->type == Tickets::TYPE_MASTER ? (TicketsDepartments::itemAlias('MasterList') ?: []) : (TicketsDepartments::itemAlias('List') ?: []), [
                    'prompt' => Yii::t('tickets', 'Select...'),
                    'disabled' => $parent_id > 0 ? true : false,
                    'allowClear' => true
                ]); ?>
            </div>
            <div class='col-md-6'>
                <?= $form->field($model, 'priority')->dropDownList(Tickets::itemAlias('Priority'), ['prompt' => Yii::t('app', 'Select...')]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'title')->label(Yii::t('app', 'Subject')) ?>
            </div>

            <?php if ($model->type != Tickets::TYPE_MASTER): ?>
                <div class="col-md-4 date-input">
                    <?= $form->field($model, 'due_date')->widget(MaskedInput::class, [
                        'mask' => '9999/99/99',
                    ]) ?>
                </div>
            <?php endif; ?>
            <div class="col-md-12">
                <?= $form->field($model, 'des')->textarea(['rows' => 6, 'placeholder' => Yii::t('app', 'Describe here...')])->label(false) ?>
            </div>
            <div class="col-md-12">
                <?= $form->field($model, 'file')->fileInput()->label(Yii::t('app', 'Attach File') . '<br/><small>پسوند هایی که پشتیبانی میشوند: jpg, png, jpeg, pdf, xlsx, mp4</small>') ?>
                <p></p>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <?= Html::submitButton(Yii::t('app', 'Send'), ['class' => 'btn btn-lg btn-success ']) ?>
    </div>

    <div class="clearfix"></div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$script = <<< JS

$('#tickets-file').on('change', function() {
	var input = $(this);
	var value = input.val();
	var subtitle = $('.field-tickets-file label > small');

	if(value) {
		subtitle.html(value);
	} else {
		subtitle.html('پسوند هایی که پشتیبانی میشوند: jpg, png, jpeg, pdf, xlsx, mp4')
	}
});


var ajax_form =jQuery('#ajax-form-comment-answer');
setTimeout(function() { $("form#ajax-form-comment-answer #comments-title").focus(); }, 200);

$(document).ready(() => {
    $('#sms-checkbox').on('change', function () {
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
// ajax_form.on('beforeSubmit', function(e) {
//     e.preventDefault();
//     var form = $(this); 
   
//     var ajax_url = form.attr('action');
//     // return false if form still have some validation errors
//     if (form.find('.has-error').length) {
//         return false;
//     }
    
//     var formdata = false;
//     if (window.FormData){
//         formdata = new FormData(form[0]);
//     }
    
//     $.ajax({
//         url: ajax_url,
//         type: 'post',
//         dataType: 'json',
// 		data: formdata ? formdata : form.serialize(),
//         cache       : false,
//         contentType : false,
//         processData : false,
//         success: function (response) {
            
//             if (response.success) {
//                 $('#modal').modal('hide');
//                 $('#modal').find('#modalContent').html('');

//                 if($('#mail_box').length) {
//                     $.pjax.reload({container: '#mail_box', timeout: false});
//                 }
//             } else {
//                 $('#modal').find('#modalContent').html(response.data);
//             }
//         },
//         error: function (e) {
//             showtoast(e.responseText, 'error');
//         }

//     });//ajax
//     return false;
// });
JS;
$this->registerJs($script);


?>
