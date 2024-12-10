<?php

use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel hesabro\ticket\models\TicketsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t("app", "Inbox");
$this->params['breadcrumbs'][] = $this->title;


$action = $action ?? Yii::$app->controller->action->id;
?>

<?php Pjax::begin(['id' => 'mail_box']); ?>
    <div class='card overflow-hidden chat-application'>
        <div class='d-flex w-100'>
            <?= $this->render('_side', ['action' => $action, 'searchModel' => $searchModel]) ?>
            <div class='d-flex w-100'>
                <?= $this->render('_list_mails', ['dataProvider' => $dataProvider, 'searchModel' => $searchModel]) ?>
                <div class='w-100'>
                    <div id="thread_box" class='chat-container h-100 w-100'>

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php Pjax::end();

$ajax_url = Url::to(['thread']);
$thread_id = Yii::$app->request->get('thread_id');
$js = <<< JS
var thread_id = "$thread_id";
if(thread_id){
    showThread(thread_id);
}
$('.mail-view-link').on('click',function (){
        var id = $(this).data('mail_id');
        showThread(id);
});

function showThread(id) {
    $('#thread_box').html('<div class="text-center" style="margin-top: 250px; font-weight: bold; font-size: 25px"><div class="spinner-grow" role="status"> <span class="visually-hidden">Loading...</span></div> <br> لطفا صبر کنید ...</div>').attr('disabled', 'disabled');
    $.ajax({
        url: '$ajax_url?id=' + id,
        type: 'GET',
        success: function (response) {
            if (response.success) {
              $('#thread_box').html(response.data);
            } else {
               showtoast(response.msg, 'error');
            }
        },
        error: function (e) {
            alert('خطایی رخ داده است.');
        }
    });//ajax
}


$(document).ready(function () {
    $(document).on('click', '#file-upload', function() {
        $('#tickets-file').click();
    });
        
    $(document).on('beforeSubmit', '#reply-form', function (e) {
        debugger;
        e.preventDefault();
        e.stopPropagation()
        const form = $(this);
        const formElement = this; // Get the raw HTML form element
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: new FormData(formElement), // Use the raw HTML form element
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
                form.find('textarea[name="Tickets[des]"]').val('')
            }
        })
    });
});
JS;
$this->registerJs($js);
?>
