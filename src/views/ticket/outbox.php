<?php

use hesabro\helpers\widgets\grid\GridView;
use hesabro\ticket\models\Tickets;
use hesabro\ticket\models\TicketsSearch;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel TicketsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t("app", "Outbox");

$action = Yii::$app->controller->action->id;
$inbox_count = Tickets::countInbox();
$doing_count = Tickets::countInbox(Tickets::STATUS_DOING);
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
				<?= Html::a($t = Yii::t('app', 'New Ticket'), ['ticket/send'], [
					'title' => $t,
					'class' => 'btn btn-info showModalButton'
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
				return ['data-id' => $model->id];
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
					'attribute' => 'priority',
					'value' => function (Tickets $model) {
						return '<span class="badge badge-' . Tickets::itemAlias('PriorityClass', $model->priority) . '">' . Tickets::itemAlias('Priority', $model->priority) . '</span>';
					},
					'filter' => false,
					'format' => 'raw',
					'contentOptions' => ['class' => 'view-message', 'style' => 'width:30px'],
				],
                [
                    'attribute' => 'department_id',
                    'value' => 'department.title'
                ],
				[
					'attribute' => 'title',
					'value' => function ($model) {
						return '<span>' . $model->title . Html::a($model->title, ['ticket/view', 'id' => $model->id], [
							'title' => $model->title,
							'id' => 'link-to-' . $model->id,
							'class' => 'showModalButton d-none',
							'data-size' => 'modal-lg'
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
						return '<span class="badge badge-' . Tickets::itemAlias('CssStatus', $model->status) . '">' . Tickets::itemAlias('Status', $model->status) . '</span>';
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
					'attribute' => 'owner',
					'value' => function ($model) {
						return $model->getOwnerList(false);
					},
					'filter' => false,
					'format' => 'raw',
					'contentOptions' => ['class' => 'view-message'],
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