<?php

namespace hesabro\ticket\controllers;

use Exception;
use hesabro\helpers\traits\AjaxValidationTrait;
use hesabro\ticket\models\Tickets;
use hesabro\ticket\models\TicketsSearch;
use hesabro\ticket\TicketModule;
use Yii;
use yii\bootstrap4\Html;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * CommentsController implements the CRUD actions for Comments model.
 */
class TicketController extends Controller
{
    use AjaxValidationTrait;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create', 'send', 'reply', 'refer'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['inbox', 'outbox', 'view', 'thread', 'change-status', 'index'],
                        'roles' => ['comments/inbox'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'reply' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex($outbox = false)
    {
        $searchModel = new TicketsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, outbox: $outbox);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionInbox()
    {
        $searchModel = new TicketsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('inbox_old', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Comments models.
     * @return mixed
     */
    public function actionOutbox()
    {
        $searchModel = new TicketsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, outbox: true);

        return $this->render('outbox', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionView($id)
    {
        $model = $this->findModel($id);
        $model->setViewed();

        return $this->renderAjax('view', [
            'model' => $model,
        ]);
    }


    public function actionThread($id)
    {
        $thread = $this->findModel($id);
        $thread->setViewed();
        if(Yii::$app->request->isAjax){
            return $this->asJson([
                'success' => true,
                'data' => $this->renderPartial('_thread', ['thread' => $thread]),
            ]);
        }

        $searchModel = new TicketsSearch();
        $tickets = $searchModel->searchMyDirectTickets(Yii::$app->request->queryParams);

        return $this->render('thread', [
            'thread' => $thread,
            'tickets' => $tickets
        ]);
    }

    public function actionReply($id)
    {
        $request = Yii::$app->request;
        $thread = $this->findModel($id);

        $model = new Tickets(['scenario' => Tickets::SCENARIO_SEND]);

        $model->due_date = Yii::$app->jdate->date('Y/m/d');
        if ($request->isPost) {
            $model->kind = Tickets::KIND_THREAD;
            $model->creator_id = Yii::$app->user->getId();
            $model->department_id = $thread->department_id;
            $model->priority = $thread->priority;
            $model->status = $thread->status;
            $model->title = $thread->title;
            $model->parent_id = $thread->parent_id ?: $thread->id;
            $model->class_name = $thread->class_name;
            $model->class_id = $thread->class_id;
            $model->link = $thread->link;
            $model->type = $thread->type;
            if(TicketModule::getInstance()->hasSlaves && Yii::$app->client->isMaster()){
                $model->slave_id = $thread->slave_id;
            }
            $model->owner = [
                ...array_map(fn ($user) => $user->id, $thread->users)
            ];
            $db = TicketModule::getInstance()->db;
            $transaction = Yii::$app->$db->beginTransaction();
            try {
                $flag = $model->load($request->post());
                $flag = $flag && $model->save();
                $flag = $flag && $model->saveInbox();

                if ($flag) {
                    $transaction->commit();
                    return $this->asJson([
                        'success' => true,
                        'msg' => Yii::t('app', 'Item Created')
                    ]);
                }

                $transaction->rollBack();
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::error($e->getMessage() . $e->getTraceAsString(),  __METHOD__ . ':' . __LINE__);
            }

            $this->response->statusCode = 400;
            return $this->asJson([
                'success' => true,
                'msg' => Yii::t('app', 'Error In Save Info')
            ]);
        }

        return $this->renderAjax('_reply', [
            'thread' => $thread,
            'model' => $model,
        ]);
    }

    public function actionRefer($id)
    {
        $comment = $this->findModel($id);
        $request = Yii::$app->request;
        $model = new Tickets([
            'parent_id' => $comment->parent_id ?: $comment->id,
            'creator_id' => Yii::$app->user->getId(),
            'kind' => Tickets::KIND_REFER,
            'type' => Tickets::TYPE_PRIVATE,
            'class_name' => $comment->class_name,
            'class_id' => $comment->class_id,
            'link' => $comment->link,
            'title' => $comment->title,
            'priority' => $comment->priority,
            'status' => $comment->status,
            'due_date' => $comment->due_date,
            'file_name' => $comment->file_name,
            'is_duty' => $comment->is_duty,
            'direct_parent_id' => $comment->id,
            'referrer_url' => $comment->referrer_url,
            'scenario' => Tickets::SCENARIO_REFER
        ]);

        if ($request->isPost) {
            $success = false;

            if ($model->load($request->post()) && $model->validate()) {
                $db = TicketModule::getInstance()->db;
                $transaction = Yii::$app->$db->beginTransaction();

                try {
                    if ($success = ($model->save() && $model->saveInbox())) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                    }
                } catch (Exception $e) {
                    $transaction->rollBack();
                    Yii::error($e->getMessage() . $e->getTraceAsString(), 'ticket/refer');
                }
            }

            $model->hasErrors() && $this->performAjaxValidation($model);
            return $this->asJson([
                'success' => $success,
                'msg' => Yii::t('app', $success ? 'Item Referred' : 'Error In Save Info')
            ]);
        }

        return $this->renderAjax('_refer', [
            'model' => $model,
            'comment' => $comment
        ]);
    }

    /**
     * @param $title
     * @param null $class_name
     * @param null $class_id
     * @param null $link
     * @return false|string
     * 
     */
    public function actionCreate($title, $class_name = null, $class_id = null, $link = null)
    {
        $response = ['success' => false, 'data' => '', 'msg' => 'خطا در ثبت اطلاعات.'];

        $model = new Tickets(['scenario' => Tickets::SCENARIO_CREATE]);
        $model->due_date = Yii::$app->jdate->date('Y/m/d');
        if ($model->load(Yii::$app->request->post())) {
            $model->class_name = $class_name;
            $model->title = $title;
            $model->class_id = $class_id;
            $model->link = $link;
            $model->type = is_array($model->owner) ? Tickets::TYPE_PRIVATE : Tickets::TYPE_PUBLIC;
            $db = TicketModule::getInstance()->db;
            $transaction = Yii::$app->$db->beginTransaction();
            try {
                if ($flag = $model->save()) {
                    $flag = $flag && $model->saveInbox();
                    if ($flag) {
                        $transaction->commit();
                        $response['success'] = true;
                        $response['msg'] = Yii::t("app", "Item created");
                        $model = new Tickets();
                        $response['data'] = $this->renderAjax('_form', [
                            'model' => $model,
                            'title' => $title,
                            'class_name' => $class_name,
                            'class_id' => $class_id,
                            'link' => $link,
                            'comments' => Tickets::find()->byClass($class_name, $class_id)->orderBy(['id' => SORT_DESC])->all(),
                        ]);
                    } else {
                        $transaction->rollBack();
                        $response['data'] = $this->renderAjax('_form', [
                            'model' => $model,
                            'title' => $title,
                            'class_name' => $class_name,
                            'class_id' => $class_id,
                            'link' => $link,
                            'comments' => Tickets::find()->byClass($class_name, $class_id)->orderBy(['id' => SORT_DESC])->all(),
                        ]);
                    }
                } else {
                    $response['data'] = $this->renderAjax('_form', [
                        'model' => $model,
                        'title' => $title,
                        'class_name' => $class_name,
                        'class_id' => $class_id,
                        'link' => $link,
                        'comments' => Tickets::find()->byClass($class_name, $class_id)->orderBy(['id' => SORT_DESC])->all(),
                    ]);
                }
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::error($e->getMessage() . $e->getTraceAsString(),  __METHOD__ . ':' . __LINE__);
            }

            return json_encode($response);
        } else {
            return $this->renderAjax('_form', [
                'model' => $model,
                'title' => $title,
                'class_name' => $class_name,
                'class_id' => $class_id,
                'link' => $link,
                'comments' => Tickets::find()->byClass($class_name, $class_id)->orderBy(['id' => SORT_DESC])->all(),
            ]);
        }
    }


    /**
     * Creates a new Comments model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionSend($title = '', $owner = false, $parent_id = 0, $type = null, $master = false)
    {
        $response = ['success' => false, 'data' => '', 'msg' => 'خطا در ثبت اطلاعات.', 'pjax_div_id' => 'details-payment'];

        $model = new Tickets();
        $model->setScenario(Tickets::SCENARIO_SEND);
        if($master){
            $model->type = Tickets::TYPE_MASTER;
        } else {
            $model->type = $type ?: (is_array($model->owner) ? Tickets::TYPE_PRIVATE : ($parent_id > 0 ? Tickets::TYPE_PRIVATE : ($model->department_id ? Tickets::TYPE_DEPARTMENT : Tickets::TYPE_PUBLIC)));
        }
        $model->referrer_url = Yii::$app->request->referrer;
        if ($model->load(Yii::$app->request->post())) {
            $model->parent_id = $parent_id;
            if ($owner) {
                $model->owner = [$owner];
            }
            $db = TicketModule::getInstance()->db;
            $transaction = Yii::$app->$db->beginTransaction();
            try {
                if ($flag = $model->save()) {
                    $flag = $flag && $model->saveInbox();
                    if ($flag) {
                        $transaction->commit();
                        $response['success'] = true;
                        $response['msg'] = Yii::t("app", "Item created");
                        $response['data'] = $this->renderAjax('_send', [
                            'model' => $model,
                            'owner' => $owner,
                            'parent_id' => $parent_id,
                        ]);
                    } else {
                        $transaction->rollBack();
                        $response['msg'] = $model->errors ? Html::errorSummary($model) : Yii::t('app', 'Error In Save Info');
                        $response['data'] = $this->renderAjax('_send', [
                            'model' => $model,
                            'owner' => $owner,
                            'parent_id' => $parent_id,
                        ]);
                    }
                } else {
                    $response['data'] = $this->renderAjax('_send', [
                        'model' => $model,
                        'owner' => $owner,
                        'parent_id' => $parent_id,
                    ]);
                }
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::error($e->getMessage() . $e->getTraceAsString(),  __METHOD__ . ':' . __LINE__);
            }

            return $this->asJson($response);
        } else {
            $model->owner = $owner;
            $model->title = $title;
            $model->priority = Tickets::PRIORITY_MEDIUM;
            
            return $this->renderAjax('_send', [
                'model' => $model,
                'owner' => $owner,
                'parent_id' => $parent_id,
            ]);
        }
    }

    public function actionChangeStatus($id, $type)
    {
        $result = [
            'status' => false,
            'message' => Yii::t("app", "Error In Save Info")
        ];

        $model = $this->findModel($id);

        if (!$model->canChangeStatus()) {
            $result['message'] = 'امکان تغییر وضعیت تیکت برای شما وجود ندارد.';
        } else {
            $statuses = Tickets::itemAlias('Status');
            if (array_key_exists($type, $statuses)) {
                $model->assigned_to = Yii::$app->user->id;
                Tickets::updateAll(['status' => $type, new Expression('JSON_SET(additional_data, "$.assigned_to", ' . Yii::$app->user->id . ')')], ['OR', ['id' => $model->parent_id ?: $model->id], ['parent_id' => $model->parent_id ?: $model->id]]);
                $result = [
                    'status' => true,
                    'message' => Yii::t("app", "Item Updated")
                ];
            } else {
                $result = [
                    'status' => false,
                    'message' => 'امکان تغیر وضعیت وجود ندارد.'
                ];
            }
        }

        return $this->asJson($result);
    }

    /**
     * Finds the Comments model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Tickets the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Tickets::find()->andWhere([Tickets::tableName() . '.id' => $id])->my()->limit(1)->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    public function flash($type, $message)
    {
        Yii::$app->getSession()->setFlash($type == 'error' ? 'danger' : $type, $message);
    }
}
