<?php

use hesabro\helpers\widgets\grid\GridView;
use hesabro\ticket\models\Comments;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CommentsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t("app", "Inbox");
$this->params['breadcrumbs'][] = $this->title;


$action = Yii::$app->controller->action->id;
$inbox_count = Comments::countInbox();
$doing_count = Comments::countInbox(Comments::STATUS_DOING);
?>

<?php Pjax::begin(['id' => 'mail_box']); ?>
<div class="card">
	<div class="panel-group m-bot20" id="accordion">
		<div class="card-header d-flex justify-content-between">
			<h4 class="panel-title">
				<a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false">
					<i class="far fa-search"></i> جستجو
				</a>
			</h4>
			<div>
				<?= Html::button($t = Yii::t('app', 'New Ticket'), [
                    'class' => 'btn btn-info',
                    'title' => $t,
                    'data-title' => $t,
                    'data-size' => 'modal-xl',
                    'data-toggle' => 'modal',
                    'data-target' => '#modal-pjax',
                    'data-url' => Url::to(['/ticket/send']),
                    'data-reload-pjax-container' => 'mail_box',
				]) ?>
			</div>
		</div>
		<div id="collapseOne" class="panel-collapse collapse" aria-expanded="false">
			<?php echo $this->render('_search', ['model' => $searchModel]); ?>
		</div>
	</div>
	<div class="card-body">

		<?= $this->render('_nav', ['searchModel' => $searchModel]); ?>
		<?= GridView::widget([
			'dataProvider' => $dataProvider,
			'tableOptions' => ['class' => 'table table-inbox table-hover'],
			'emptyCell' => '-',
			'rowOptions' => function ($model, $index, $widget, $grid) {
				if ($model->viewed == 1) {
					return ['class' => 'warning font-bold', 'data-id' => $model->id];
				} else {
					return ['data-id' => $model->id];
				}
			},
			'columns' => [
				[
					'class' => 'yii\grid\SerialColumn',
					'contentOptions' => ['class' => 'view-message', 'style' => 'width:50px'],
				],
				[
					'attribute' => 'id',
					'filter' => false,
					'format' => 'raw',
					'contentOptions' => ['class' => 'view-message', 'style' => 'width:100px;text-align: right'],
					'headerOptions' => ['style' => 'text-align: right'],
				],
				[
					'attribute' => 'css_class',
					'value' => function ($model) {
						return '<span class="badge badge-' . Comments::itemAlias('CssClass', $model->css_class) . '">' . Comments::itemAlias('Type', $model->css_class) . '</span>';
					},
					'filter' => false,
					'format' => 'raw',
					'contentOptions' => ['class' => 'view-message', 'style' => 'width:30px'],
				],
				[
					'attribute' => 'title',
					'value' => function ($model) {
						return '<span>' . $model->title . Html::a($model->title, ['/ticket/view', 'id' => $model->id], [
							'title' => $model->title,
							'id' => 'link-to-' . $model->id,
							'class' => 'showModalButton d-none',
							'data-size' => 'modal-xl'
						]) . '</span>';
					},
					'filter' => false,
					'format' => 'raw',
					'contentOptions' => ['class' => 'view-message', 'style' => 'text-align: right'],
					'headerOptions' => ['style' => 'text-align: right'],
				],
				[
					'attribute' => 'status',
					'value' => function ($model) {
						return '<span class="badge badge-' . Comments::itemAlias('CssStatus', $model->status) . '">' . Comments::itemAlias('Status', $model->status) . '</span>';
					},
					'filter' => false,
					'format' => 'raw',
					'contentOptions' => ['class' => 'view-message', 'style' => 'width:100px;text-align: center'],
					'headerOptions' => ['style' => 'text-align: center'],
				],
				[
					'attribute' => 'due_date',
					'value' => function ($model) {
						return $model->due_date;
					},
					'filter' => false,
					'contentOptions' => ['class' => 'view-message', 'style' => 'width:120px;text-align: right'],
					'headerOptions' => ['style' => 'text-align: right'],
				],
				[
					'attribute' => 'creator_id',
					'value' => function ($model) {
						return $model->creator?->fullName;
					},
					'filter' => false,
					'contentOptions' => ['class' => 'view-message', 'style' => 'width:120px;text-align: right'],
					'headerOptions' => ['style' => 'text-align: right'],
				],
				[
					'attribute' => 'created',
					'value' => function ($model) {
						return Yii::$app->jdate->date('Y/m/d H:i', $model->created);
					},
					'filter' => false,
					'contentOptions' => ['class' => 'view-message', 'style' => 'width:120px;text-align: right'],
					'headerOptions' => ['style' => 'text-align: right'],
				],
			],
		]); ?>
	</div>
</div>
<?php

$script = <<< JS
	$('td').click(function (e) {
		if(e.target == this) {
			$('#link-to-' + $(this).closest('tr').data('id')).click();
		}
	});
JS;

$this->registerJs($script);

Pjax::end(); ?>