<?php

use hesabro\helpers\components\iconify\Iconify;
use hesabro\ticket\models\Comments;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $thread Comments */
/* @var $model Comments */
/* @var $form yii\bootstrap4\ActiveForm */

?>

<div>
    <?php $form = ActiveForm::begin([
        'id' => 'reply-form',
        'action' => Url::to(['ticket/reply', 'id' => $thread->id ])
    ]); ?>
    <input type="file" id="comments-file" class="d-none" name="<?= $model->formName() ?>[file]" aria-invalid="false" value />
    <div class="d-flex align-items-center justify-content-start">
        <div style="flex: 1">
            <?= $form->field($model, 'des', ['options' => ['tag' => false]])->textarea(['row' => 1, 'class' => 'form-control form-control-lg border-0', 'placeholder' => 'پاسخ خود را شرح دهید...'])->label(false) ?>
        </div>
        <div>
            <?= Html::button('<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"><path fill="currentColor" d="M7.5 18A5.5 5.5 0 0 1 2 12.5A5.5 5.5 0 0 1 7.5 7H18a4 4 0 0 1 4 4a4 4 0 0 1-4 4H9.5A2.5 2.5 0 0 1 7 12.5A2.5 2.5 0 0 1 9.5 10H17v1.5H9.5a1 1 0 0 0-1 1a1 1 0 0 0 1 1H18a2.5 2.5 0 0 0 2.5-2.5A2.5 2.5 0 0 0 18 8.5H7.5a4 4 0 0 0-4 4a4 4 0 0 0 4 4H17V18z"/></svg>', ['id' => 'file-upload', 'class' => 'btn btn-text']) ?>
            <button class="btn btn-text" type="submit" value="ignore-disabled" style="box-shadow: unset !important; rotate: 180deg">
                <span class="text">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"><path fill="currentColor" d="M4.4 19.425q-.5.2-.95-.088T3 18.5V14l8-2l-8-2V5.5q0-.55.45-.837t.95-.088l15.4 6.5q.625.275.625.925t-.625.925z"/></svg>
                </span>
                <span class="loading font-32" style="display: none"><?= Iconify::getInstance()->icon('svg-spinners:90-ring-with-bg') ?></span>
            </button>
            <div class="py-2 px-3">
                <?= $form->field($model, 'send_sms', ['options' => ['class' => 'mb-0']])->checkbox() ?>
            </div>
        </div>

    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php
$pjaxUrl = Url::to(['ticket/thread', 'id' => $thread->id]);
$script = <<< JS
$(document).ready(function () {
    const scrollToEnd = () => {
        const messagesList = $('#messages-container').find('div:first')
        messagesList.animate({ scrollTop: messagesList[0].scrollHeight }, 10, 'swing')
    }
    
    $('#file-upload').on('click', function() {
        $('#comments-file').click();
    })

    $('#messages-container').on('pjax:end', function () {
        scrollToEnd()
    })
    
    scrollToEnd()
    
    $('#reply-form').on('beforeSubmit', function (e) {
        e.preventDefault();
        e.stopPropagation()
        const form = $(this)
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: new FormData(this),
            processData: false, 
            contentType: false,
            beforeSend: function () {
                form.find('button[type="submit"]').attr('disabled', 'disabled')
                form.find('button[type="submit"] .text').hide()
                form.find('button[type="submit"] .loading').show()
            },
            complete: function () {
                form.find('button[type="submit"] .loading').hide()
                form.find('button[type="submit"] .text').show()
                form.find('button[type="submit"]').removeAttr('disabled')
            },
            success: function () {
                form.find('textarea[name="Comments[des]"]').val('')
                $.pjax({
                    url: '$pjaxUrl',
                    container: '#messages-container',
                    replace: false,
                    push: false,
                    success: () => {
                        scrollToEnd()
                    }
                }) 
            }
        })
    })
    
})
JS;
$this->registerJs($script);
?>