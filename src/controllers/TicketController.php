<?php

namespace hesabro\ticket\controllers;

use Exception;
use hesabro\helpers\traits\AjaxValidationTrait;
use hesabro\ticket\models\Comments;
use hesabro\ticket\models\CommentsSearch;
use Yii;
use yii\bootstrap4\Html;
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
                        'actions' => ['inbox', 'outbox', 'view', 'thread', 'change-status'],
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

    public function actionInbox()
    {
        $searchModel = new CommentsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('inbox', [
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
        $searchModel = new CommentsSearch();
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

        $searchModel = new CommentsSearch();
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

        $model = new Comments(['scenario' => Comments::SCENARIO_SEND]);

        $model->due_date = Yii::$app->jdate->date('Y/m/d');
        if ($request->isPost) {
            $model->kind = Comments::KIND_THREAD;
            $model->creator_id = Yii::$app->user->getId();
            $model->css_class = $thread->css_class;
            $model->status = $thread->status;
            $model->title = $thread->title;
            $model->parent_id = $thread->parent_id ?: $thread->id;
            $model->class_name = $thread->class_name;
            $model->class_id = $thread->class_id;
            $model->link = $thread->link;
            $model->type = $thread->type;
            $model->owner = [
                ...array_map(fn ($user) => $user->id, $thread->users)
            ];
            $transaction = Yii::$app->db->beginTransaction();
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
        $model = new Comments([
            'parent_id' => $comment->parent_id ?: $comment->id,
            'creator_id' => Yii::$app->user->getId(),
            'kind' => Comments::KIND_REFER,
            'type' => Comments::TYPE_PRIVATE,
            'class_name' => $comment->class_name,
            'class_id' => $comment->class_id,
            'link' => $comment->link,
            'title' => $comment->title,
            'css_class' => $comment->css_class,
            'status' => $comment->status,
            'due_date' => $comment->due_date,
            'file_name' => $comment->file_name,
            'is_duty' => $comment->is_duty,
            'direct_parent_id' => $comment->id,
            'master_task_type_id' => $comment->master_task_type_id,
            'referrer_url' => $comment->referrer_url,
            'scenario' => Comments::SCENARIO_REFER
        ]);

        if ($request->isPost) {
            $success = false;

            if ($model->load($request->post()) && $model->validate()) {
                $transaction = Yii::$app->db->beginTransaction();

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

        $model = new Comments(['scenario' => Comments::SCENARIO_CREATE]);
        $model->due_date = Yii::$app->jdate->date('Y/m/d');
        if ($model->load(Yii::$app->request->post())) {
            $model->class_name = $class_name;
            $model->title = $title;
            $model->class_id = $class_id;
            $model->link = $link;
            $model->type = is_array($model->owner) ? Comments::TYPE_PRIVATE : Comments::TYPE_PUBLIC;
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($flag = $model->save()) {
                    $flag = $flag && $model->saveInbox();
                    if ($flag) {
                        $transaction->commit();
                        $response['success'] = true;
                        $response['msg'] = Yii::t("app", "Item created");
                        $model = new Comments();
                        $response['data'] = $this->renderAjax('_form', [
                            'model' => $model,
                            'title' => $title,
                            'class_name' => $class_name,
                            'class_id' => $class_id,
                            'link' => $link,
                            'comments' => Comments::find()->byClass($class_name, $class_id)->orderBy(['id' => SORT_DESC])->all(),
                        ]);
                    } else {
                        $transaction->rollBack();
                        $response['data'] = $this->renderAjax('_form', [
                            'model' => $model,
                            'title' => $title,
                            'class_name' => $class_name,
                            'class_id' => $class_id,
                            'link' => $link,
                            'comments' => Comments::find()->byClass($class_name, $class_id)->orderBy(['id' => SORT_DESC])->all(),
                        ]);
                    }
                } else {
                    $response['data'] = $this->renderAjax('_form', [
                        'model' => $model,
                        'title' => $title,
                        'class_name' => $class_name,
                        'class_id' => $class_id,
                        'link' => $link,
                        'comments' => Comments::find()->byClass($class_name, $class_id)->orderBy(['id' => SORT_DESC])->all(),
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
                'comments' => Comments::find()->byClass($class_name, $class_id)->orderBy(['id' => SORT_DESC])->all(),
            ]);
        }
    }


    /**
     * Creates a new Comments model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionSend($title = '', $owner = false, $parent_id = 0, $type = null)
    {
        $response = ['success' => false, 'data' => '', 'msg' => 'خطا در ثبت اطلاعات.', 'pjax_div_id' => 'details-payment'];

        $model = new Comments();
        $model->setScenario($type == Comments::TYPE_MASTER ? Comments::SCENARIO_MASTER : Comments::SCENARIO_SEND);
        $model->type = $type;
        $model->referrer_url = Yii::$app->request->referrer;
        if ($model->load(Yii::$app->request->post())) {
            $model->parent_id = $parent_id;
            $model->type = $type ?: (is_array($model->owner) ? Comments::TYPE_PRIVATE : ($parent_id > 0 ? Comments::TYPE_PRIVATE : Comments::TYPE_PUBLIC));
            if ($owner) {
                $model->owner = [$owner];
            }
            $transaction = Yii::$app->db->beginTransaction();
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
            $model->master_task_type_id = Comments::MASTER_TASK_TYPE_TECHNICAL;
            $model->css_class = Comments::TYPE_SUCCESS;
            
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
            $statuses = Comments::itemAlias('Status');
            if (array_key_exists($type, $statuses)) {
                Comments::updateAll(['status' => $type], ['OR', ['id' => $model->parent_id ?: $model->id], ['parent_id' => $model->parent_id ?: $model->id]]);
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
     * @return Comments the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Comments::find()->andWhere(['id' => $id])->my()->limit(1)->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    public function flash($type, $message)
    {
        Yii::$app->getSession()->setFlash($type == 'error' ? 'danger' : $type, $message);
    }
}
