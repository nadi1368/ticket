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
$js = <<< JS
$('.mail-view-link').on('click',function (){
        $('#thread_box').html('<div class="spinner-grow" role="status"> <span class="visually-hidden">Loading...</span></div> لطفا صبر کنید ...').attr('disabled', 'disabled')
        var id = $(this).data('mail_id');
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
})
JS;
$this->registerJs($js);
?>
