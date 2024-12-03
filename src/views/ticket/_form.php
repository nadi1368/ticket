<?php

use hesabro\ticket\models\Tickets;
use kartik\select2\Select2;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\MaskedInput;

/* @var $this yii\web\View */
/* @var $model \hesabro\ticket\models\Tickets */
/* @var $comments \hesabro\ticket\models\Tickets[] */
/* @var $form yii\bootstrap4\ActiveForm */

$url = Url::to([
	'ticket/create',
	'title' => $title,
	'class_name' => $class_name,
	'class_id' => $class_id,
	'link' => $link,
]);

?>

<div class="comments-form">

	<?php $form = ActiveForm::begin([
		'action' => $url,
		'id' => 'ajax-form-comments',
		'options' => [
			'enctype' => "multipart/form-data",
		],
	]); ?>
	<div class="card">
		<div class="card-body">
			<div class="row">
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
				<div class="col-md-4">
					<?= $form->field($model, 'priority')->dropDownList(Tickets::itemAlias('Priority'), ['prompt' => Yii::t('app', 'Select...')]) ?>
				</div>

				<div class="col-md-4">
					<?= $form->field($model, 'due_date')->widget(MaskedInput::class, [
						'mask' => '9999/99/99',
						'options' => ['disabled' => true],
					]) ?>
				</div>
				<div class="col-md-6">
					<?= $form->field($model, 'file')->fileInput() ?>
					<p>پسوند هایی که پشتیبانی میشوند: jpg, png, jpeg, pdf, xlsx, mp4</p>
				</div>

				<div class="col-md-12">
					<?= $form->field($model, 'des')->textarea(['rows' => 3]) ?>
				</div>
			</div>
		</div>
		<div class="card-footer">
			<?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success ']) ?>
		</div>
	</div>

	<?php ActiveForm::end(); ?>

</div>


<?= $this->render('_view', [
	'comments' => $comments,
]) ?>

<?php
$script = <<< JS
var ajax_form =jQuery('#ajax-form-comments');
setTimeout(function() { $("form#ajax-form-comments #comments-des").focus(); }, 200);
ajax_form.on('beforeSubmit', function(e) {
    e.preventDefault();
    var form = $(this); 
    
    var ajax_url = form.attr('action');
    // return false if form still have some validation errors
    if (form.find('.has-error').length) {
        return false;
    }
    
    var formdata = false;
    if (window.FormData){
        formdata = new FormData(form[0]);
    }
    
    $.ajax({
        url: ajax_url,
        type: 'post',
        dataType: 'json',
        data: formdata ? formdata : form.serialize(),
        cache       : false,
        contentType : false,
        processData : false,
        success: function (response) {
            
            if (response.success) {
                $('#modal').find('#modalContent').html(response.data);
                showtoast(response.msg, 'success');
            } else {
                $('#modal').find('#modalContent').html(response.data);
                showtoast(response.msg, 'error');
            }
            
        },
        error: function (e) {
            alert('خطایی رخ داده است.');
        }

    });//ajax
    return false;
});
JS;
$this->registerJs($script);


?>
