<?php

use common\widgets\grid\GridView;
use hesabro\ticket\models\TicketsDepartments;
use yii\bootstrap4\ButtonDropdown;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel \hesabro\ticket\models\TicketsDepartmentsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('tickets', 'Tickets Departments');
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(['id' => 'tickets-departments-p-jax']); ?>
<div class="tickets-departments-index card">
    <div class="panel-group m-bot20" id="accordion">
        <div class="card-header d-flex justify-content-between">
            <h4 class="panel-title">
                <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion"
                   href="#collapseOne" aria-expanded="false">
                    <i class="far fa-search"></i> جستجو
                </a>
            </h4>
            <div>
                <?= Html::a('ایجاد',
                    "javascript:void(0)",
                    [
                        'id' => 'tickets-departments-create',
                        'class' => 'grid-btn grid-btn-update btn btn-success',
                        'data-size' => 'modal-xl',
                        'data-title' => Yii::t('app', 'Create'),
                        'data-toggle' => 'modal',
                        'data-target' => '#modal-pjax',
                        'data-url' => Url::to(['create']),
                        'data-reload-pjax-container' => 'tickets-departments-p-jax',
                        'disabled' => true
                    ]); ?>
            </div>
        </div>
        <div id="collapseOne" class="panel-collapse collapse" aria-expanded="false">
            <?php echo $this->render('_search', ['model' => $searchModel]); ?>
        </div>
    </div>
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                'id',
                'title',
                [
                    'attribute' => 'user_ids',
                    'value' => function (TicketsDepartments $model) {
                        $data = '';
                        foreach ($model->users as $user) {
                            $data .= Html::tag('span', $user->fullName, ['class' => 'badge badge-primary mt-2']) . ' ';
                        }
                        return $data;
                    },
                    'format' => 'raw',
                ],
                [
                    'class' => 'common\widgets\grid\ActionColumn',
                    'template' => '{delete}{update}',
                    'buttons' => [
                        'update' => function ($url, $model, $key) {
                            return Html::a('<span class="far fa-edit"></span>',
                                'javascript:void(0)', [
                                    'title' => Yii::t('tickets', 'Update'),
                                    'class' => 'text-success',
                                    'data-size' => 'modal-xl',
                                    'data-title' => Yii::t('tickets', 'Update'),
                                    'data-toggle' => 'modal',
                                    'data-target' => '#modal-pjax',
                                    'data-url' => Url::to(['update', 'id' => $model->id]),
                                    'data-reload-pjax-container-on-show' => 1,
                                    'data-reload-pjax-container' => 'tickets-departments-p-jax',
                                    'data-handle-form-submit' => 1,
                                    'disabled' => true,
                                ]);
                        },
                        'delete' => function ($url, $model, $key) {
                            return $model->canDelete() ? Html::a('<span class="far fa-trash-alt text-danger"></span>', ['delete', 'id' => $key], [
                                'title' => Yii::t('yii', 'Delete'),
                                'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                'data-method' => 'post',
                            ]) : '';
                        },
                    ]
                ],
            ],
        ]); ?>
        <?php Pjax::end(); ?>
    </div>
</div>
