<?php

namespace hesabro\ticket\controllers;

use common\traits\AjaxValidationTrait;
use Exception;
use hesabro\ticket\models\TicketsDepartments;
use hesabro\ticket\models\TicketsDepartmentsSearch;
use hesabro\ticket\TicketModule;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * TicketsDepartmentsController implements the CRUD actions for TicketsDepartments model.
 */
class TicketsDepartmentsController extends Controller
{
    use AjaxValidationTrait;
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' =>
                [
                    [
                        'allow' => true,
                        'roles' => ['superadmin'],
                    ],
                ]
            ]
        ];
    }

    /**
     * Lists all TicketsDepartments models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TicketsDepartmentsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single TicketsDepartments model.
     * @param int $id آیدی
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->renderAjax('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new TicketsDepartments model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new TicketsDepartments();

        if ($this->request->isPost) {
            $db = TicketModule::getInstance()->db;
            $transaction = Yii::$app->$db->beginTransaction();
            try {
                if ($model->load(Yii::$app->request->post())) {
                    $flag = $model->save();
                    if($flag){
                        foreach ($model->user_ids ?? [] as $user_id) {
                            $model->link('users', Yii::$app->user->identityClass::findOne($user_id));
                        }
                    }
                    if($flag) {
                        $transaction->commit();
                        $result = [
                            'success' => true,
                            'msg' => Yii::t("tickets", 'Item Created'),
                        ];
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return $result;
                    } else {
                        $transaction->rollBack();
                    }
                }
            } catch (Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        } else {
            $model->loadDefaultValues();
        }

        $this->performAjaxValidation($model);
        return $this->renderAjax('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing TicketsDepartments model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id آیدی
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->user_ids = ArrayHelper::map($model->users, 'id', 'id');
        if (!$model->canUpdate()) {
            $this->flash('danger', Yii::t("tickets", "Can Not Update"));
            return $this->redirect(['index']);
        }

        if ($this->request->isPost) {
            $db = TicketModule::getInstance()->db;
            $transaction = Yii::$app->$db->beginTransaction();
            try {
                if ($model->load(Yii::$app->request->post())) {
                    $flag = $model->save();
                    if($flag){
                        $model->unlinkAll('users', true);
                        foreach ($model->user_ids ?? [] as $user_id) {
                            $model->link('users', Yii::$app->user->identityClass::findOne($user_id));
                        }
                    }
                    if($flag) {
                        $transaction->commit();
                        $result = [
                            'success' => true,
                            'msg' => Yii::t("tickets", 'Item Updated')
                        ];
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return $result;
                    } else {
                        $transaction->rollBack();
                    }
                }
            } catch (Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        $this->performAjaxValidation($model);
        return $this->renderAjax('update', [
        'model' => $model,
        ]);
    }

    /**
     * Deletes an existing TicketsDepartments model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id آیدی
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->softDelete()) {
            $this->flash('success', Yii::t("tickets", "Item Deleted"));
        }
        return $this->redirect(['index']);
    }

    /**
     * Finds the TicketsDepartments model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id آیدی
     * @return TicketsDepartments the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TicketsDepartments::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('tickets', 'The requested page does not exist.'));
    }

    public function flash($type, $message)
    {
        Yii::$app->getSession()->setFlash($type == 'error' ? 'danger' : $type, $message);
    }
}
