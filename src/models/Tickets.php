<?php

namespace hesabro\ticket\models;

use backend\modules\master\models\Client;
use hesabro\changelog\behaviors\LogBehavior;
use hesabro\errorlog\behaviors\TraceBehavior;
use hesabro\helpers\behaviors\JsonAdditional;
use hesabro\helpers\components\Jdf;
use hesabro\helpers\validators\DateValidator;
use hesabro\notif\behaviors\NotifBehavior;
use hesabro\notif\interfaces\NotifInterface;
use hesabro\ticket\jobs\SendTicketNotifJob;
use hesabro\ticket\TicketModule;
use mamadali\S3Storage\behaviors\StorageUploadBehavior;
use mamadali\S3Storage\components\S3Storage;
use mamadali\S3Storage\models\StorageFileShared;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%comments}}".
 *
 * @mixin StorageUploadBehavior
 * @mixin NotifBehavior
 *
 * @property int $id
 * @property int $parent_id
 * @property int $department_id
 * @property string $creator_id
 * @property string $update_id
 * @property int $type عمومی یا مخصول یک کاربر
 * @property int $kind
 * @property int $type_task کار یا ماموریت توضیحات و ...
 * @property string $class_name
 * @property int $class_id
 * @property string $link
 * @property string $title
 * @property string $des
 * @property int $priority
 * @property int $status
 * @property string $due_date
 * @property string $created
 * @property string $changed
 * @property string $file_name
 * @property string $additional_data
 *
 * @property TicketsView[] $ticketsViews
 * @property User[] $users
 * @property User[] $owners
 * @property User $creator
 * @property CommentsType $typeTask
 * @property string $fullTitle
 * @property string $htmlLink
 * @property string $sender
 * @property TicketsDepartments $department
 * @property User $assignedTo
 * @property self $parent
 */
class Tickets extends \yii\db\ActiveRecord implements NotifInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DOING = 2;
    const STATUS_CLOSE = 3;

    const PRIORITY_ESSENTIAL = 1;
    const PRIORITY_HIGH = 2;
    const PRIORITY_MEDIUM = 3;
    const PRIORITY_LOW = 4;

    const TYPE_PUBLIC = 0;
    const TYPE_PRIVATE = 1;
    const TYPE_DEPARTMENT = 2;
    const TYPE_MASTER = 3;

    const KIND_TICKET = 1;

    const KIND_REFER = 2;

    const KIND_THREAD = 3;

    const SCENARIO_CREATE = 'create';
    const SCENARIO_CREATE_APP = 'create_app';
    const SCENARIO_SEND = 'send';
    const SCENARIO_ANSWER = 'answer';
    const SCENARIO_AUTO = 'auto';
    const SCENARIO_REPORT_BUG = 'report_bug';
    const SCENARIO_SUPPORT = 'support';

    const SCENARIO_REFER = 'refer';

    const NOTIF_TICKET_SEND = 'notif_ticket_send';
    const NOTIF_TICKET_SEND_SUPPORT = 'notif_ticket_send_support';

    public $error_msg;
    public $owner;
    public $send_sms = 0;
    public $send_email = 0;
    public $unread;

    /** @var UploadedFile $file */
    public $file;

    // Additional Data
    public ?int $master_task_type_id = null;

    public ?string $referrer_url = null;

    public $is_duty = null;

    public mixed $direct_parent_id = null;

    public bool $useDescriptionInNotification = false;

    public bool $useTitleInNotification = false;

    public mixed $send_email_at = null;

    public mixed $send_sms_at = null;
    public $assigned_to;
    public $user_fullName;
    public $user_number;
    public $module_id;
    public $send_notif = false;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tickets}}';
    }

    public static function getDb()
    {
        return Yii::$app->get(TicketModule::getInstance()->db);
    }

    public function behaviors()
    {
        $behaviors = [
            [
                'class' => StorageUploadBehavior::class,
                'attributes' => ['file'],
                'accessFile' => S3Storage::ACCESS_PRIVATE,
                'scenarios' => [self::SCENARIO_CREATE, self::SCENARIO_CREATE_APP, self::SCENARIO_SEND],
                'path' => 'tickets/{id}',
                'storageFilesModelClass' => TicketModule::getInstance()->hasSlaves ? StorageFileShared::class : null,
                'sharedWith' => function (self $model) {
                    $clientComponentClass = TicketModule::getInstance()->clientComponentClass;
                    if($clientComponentClass && self::TYPE_MASTER){
                        return Yii::$app->client->isMaster() ? [$model->slave_id] : [$clientComponentClass::getMasterClient()->id];
                    }
                    return [];
                }
            ],
            [
                'class' => TraceBehavior::class,
                'ownerClassName' => self::class
            ],
            [
                'class' => LogBehavior::class,
                'ownerClassName' => self::class,
                'saveAfterInsert' => true
            ],
            [
                'class' => JsonAdditional::class,
                'ownerClassName' => self::class,
                'notSaveNull' => true,
                'fieldAdditional' => 'additional_data',
                'AdditionalDataProperty' => [
                    'master_task_type_id' => 'NullInteger',
                    'referrer_url' => 'String',
                    'is_duty' => 'Boolean',
                    'direct_parent_id' => 'NullInteger',
                    'assigned_to' => 'NullInteger',
                    'user_fullName' => 'NullString',
                    'user_number' => 'NullString',
                    'module_id' => 'NullInteger',
                ],
            ],
//            [
//                'class' => NotifBehavior::class,
//                'event' => self::NOTIF_TICKET_SEND,
//                'scenario' => [self::SCENARIO_SEND],
//            ],
//            [
//                'class' => NotifBehavior::class,
//                'event' => self::NOTIF_TICKET_SEND_SUPPORT,
//                'scenario' => [self::SCENARIO_SUPPORT],
//            ],
        ];

        if (TicketModule::getInstance()->notificationBehavior){
            $behaviors['NotificationBehavior'] = TicketModule::getInstance()->notificationBehavior;
        }
        return $behaviors;
    }

    public function beforeValidate()
    {

        if (in_array('send_sms_at', $this->scenarios()[$this->scenario] ?? [])) {
            $this->send_sms_at = $this->send_sms_at ? Jdf::jalaliToTimestamp($this->send_sms_at, 'Y/m/d H:i') : null;
        }

        if (in_array('send_email_at', $this->scenarios()[$this->scenario] ?? [])) {
            $this->send_email_at = $this->send_email_at ? Jdf::jalaliToTimestamp($this->send_email_at, 'Y/m/d H:i') : null;
        }

        return parent::beforeValidate();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['des', 'priority', 'title'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_SEND]],
            [['department_id'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_SEND], 'when' => function (self $model) {
                return ;
            }],
            [['department_id'], 'required', 'on' => [self::SCENARIO_REFER]],
            [['des', 'priority', 'class_id', 'due_date'], 'required', 'on' => [self::SCENARIO_CREATE_APP]],
            [['des', 'priority'], 'required', 'on' => [self::SCENARIO_REPORT_BUG]],
            [[
                'parent_id', 'type', 'creator_id', 'update_id', 'class_id', 'priority', 'status', 'created', 'changed',
                'master_task_type_id', 'send_sms', 'send_email', 'direct_parent_id'
            ], 'integer'],
            [['des', 'file_name'], 'string'],
            [['owner'], 'each', 'rule' => ['integer']],
            [['owner'], 'validateRefer', 'on' => [self::SCENARIO_REFER]],
            [['parent_id'], 'validateParentID', 'when' => function (self $model) {
                return $model->parent_id;
            }],
            [['title', 'link'], 'string', 'max' => 128],
            [['class_name'], 'string', 'max' => 64],
            [['due_date'], DateValidator::class],
            ['file', 'file',
                'extensions' => [
                    'jpg',
                    'jpeg',
                    'png',
                    'pdf',
                    'mp4',
                    'xlsx',
                    'xls',
                    'zip'
                ],
                'mimeTypes' => [
                    'image/png',
                    'image/jpg',
                    'image/jpeg',
                    'application/pdf',
                    'video/mp4',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-excel',
                    'application/zip'
                ],
                'maxSize' => 2 * 1024 * 1024],
            //[['type_task'], 'exist', 'skipOnError' => true, 'targetClass' => CommentsType::class, 'targetAttribute' => ['type_task' => 'id']],
            [['send_email_at', 'send_sms_at'], 'number', 'enableClientValidation' => false],
            [['send_email_at', 'send_sms_at'], 'validateTimeAfter'],
        ];
    }

    public function validateRefer($attribute): void
    {
        if ($this->kind === self::KIND_THREAD) {
            $this->addError($attribute, 'امکان ارجاع این تیکت وجود ندارد.');
        }

        $parent = $this->direct_parent_id ? self::findOne($this->direct_parent_id) : null;
        $parentMembers = array_map(fn($user) => (int) $user->id, $parent?->users ?: []);
        $owner = array_map(fn(int $item) => (int) $item, $this->owner ?: []);

        if (count(array_intersect($owner, $parentMembers)) > 0) {
            $this->addError($attribute, 'امکان ارجاع تیکت به اعضا حال حاظر وجود ندارد.');
        }
    }

    public function validateParentID($attribute): void
    {
        $parent = self::findOne($this->$attribute);
        if (!$parent) {
            $this->addError($attribute, 'تیکتی برای پاسخ دادن یافت نشد.');
        }
    }

    public function validateTimeAfter($attribute): void
    {
        if ($this->$attribute && $this->$attribute < time()) {
            $this->addError($attribute, $this->getAttributeLabel($attribute) . ' باید بعد از تاریخ حال باشد.');
        }
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = ['des', 'priority', 'title', 'owner', 'class_id', 'due_date', 'class_name', 'link', 'file'];
        $scenarios[self::SCENARIO_CREATE_APP] = ['des', 'priority', 'title', 'owner', 'parent_id', 'class_id', 'due_date', 'class_name', 'link', 'file'];
        $scenarios[self::SCENARIO_SEND] = ['department_id', 'des', 'priority', 'title', 'owner', 'parent_id', 'class_id', 'due_date', 'class_name', 'link', 'file', 'send_sms', 'send_email', 'send_sms_at', 'send_email_at'];
        $scenarios[self::SCENARIO_AUTO] = ['des', 'priority', 'title', 'owner', 'parent_id', 'class_id', 'due_date', 'class_name', 'link', 'send_sms', 'type_task'];
        $scenarios[self::SCENARIO_ANSWER] = ['des', 'priority', 'title', 'owner', 'parent_id', 'class_id', 'due_date', 'class_name', 'link', 'send_sms', 'type_task', 'send_sms', 'send_email', 'send_sms_at', 'send_email_at'];
        $scenarios[self::SCENARIO_REPORT_BUG] = ['des', 'priority'];
        $scenarios[self::SCENARIO_REFER] = ['department_id', 'des', 'owner', 'send_sms', 'send_email', 'send_sms_at', 'send_email_at'];

        if(TicketModule::getInstance()->hasSlaves && Yii::$app->client->isMaster()){
            $scenarios[self::SCENARIO_SEND][] = 'slave_id';
        }

        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('tickets', 'ID'),
            'creator_id' => Yii::t('tickets', 'Creator ID'),
            'update_id' => Yii::t('tickets', 'Update ID'),
            'owner' => Yii::t('tickets', 'Receivers'),
            'type_task' => Yii::t('tickets', 'Type') . ' ' . Yii::t('tickets', 'Task'),
            'class_name' => Yii::t('tickets', 'Related To'),
            'class_id' => Yii::t('tickets', 'Class ID'),
            'title' => Yii::t('tickets', 'Title'),
            'des' => Yii::t('tickets', 'Ticket Description'),
            'priority' => Yii::t('tickets', 'Priority'),
            'send_sms' => Yii::t('tickets', 'Send Sms'),
            'send_email' => Yii::t('tickets', 'Send Email'),
            'status' => Yii::t('tickets', 'Status'),
            'due_date' => Yii::t('tickets', 'Due Done'),
            'created' => Yii::t('tickets', 'Created'),
            'changed' => Yii::t('tickets', 'Changed'),
            'file' => Yii::t('tickets', 'Attach File'),
            'is_duty' => Yii::t('tickets', 'Duty'),
            'send_email_at' => Yii::t('tickets', 'Send Email Date'),
            'send_sms_at' => Yii::t('tickets', 'Send Sms Date'),
            'department_id' => Yii::t('tickets', 'Department'),
            'assigned_to' => Yii::t('tickets', 'Assigned To'),
            'slave_id' => Yii::t('tickets', 'Client'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCommentsViews()
    {
        return $this->hasMany(TicketsView::class, ['comment_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        $userModelClass = Yii::$app->user->identityClass;
        return $this->hasMany($userModelClass, ['id' => 'user_id'])->viaTable('{{%tickets_view}}', ['comment_id' => 'id']);
    }

    public function getOwners()
    {
        $userModelClass = Yii::$app->user->identityClass;
        return $this->hasMany($userModelClass, ['id' => 'owner']);
    }

    public function getCreator()
    {
        $userModelClass = Yii::$app->user->identityClass;
        return $this->hasOne($userModelClass, ['id' => 'creator_id']);
    }

    public function getTypeTask()
    {
        return $this->hasOne(CommentsType::class, ['id' => 'type_task']);
    }

    public function getDepartment(): \yii\db\ActiveQuery
    {
        return $this->hasOne(TicketsDepartments::class, ['id' => 'department_id']);
    }

    public function getDepartmentTitle(): ?string
    {
        if(!$this->department_id){
            return null;
        }
        $title = null;
        if($this->type == self::TYPE_MASTER && TicketModule::getInstance()->hasSlaves && !Yii::$app->client->isMaster()){
            Yii::$app->params['findMasterDepartments'] = true;
            $title = TicketsDepartments::findOne($this->department_id)?->title;
        }
        if(!$title){
            Yii::$app->params['findMasterDepartments'] = false;
            $title = TicketsDepartments::findOne($this->department_id)?->title;
        }
        return $title;
    }

    public function getAssignedTo()
    {
        $userModelClass = Yii::$app->user->identityClass;
        return $this->hasOne($userModelClass, ['id' => 'assigned_to']);
    }

    public function getParent()
    {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwnerModel()
    {
        $modelClass = Yii::createObject($this->class_name);
        return $this->hasOne(
            $this->class_name,
            [
                $modelClass->primaryKey()[0] => 'class_id'
            ]
        );
    }

    public function getCreatorFullName()
    {
        if($this->type == self::TYPE_MASTER){
            return $this->user_fullName;
        }
        return $this->creator?->fullName;
    }

    /**
     * {@inheritdoc}
     * @return array the active query used by this AR class.
     */
    public static function itemAlias($type, $code = NULL)
    {
        $list_data = [];
        if ($type == 'List') {
            $list = self::find()->all();
            $list_data = ArrayHelper::map($list, 'id', 'name');
        }

        if ($type == 'Owner') {
            $userClass = TicketModule::getInstance()->user;
            $list_data = $userClass::getUserWithRoles(TicketModule::getInstance()->ticketsRole);
        }

        if($type == 'ClientList'){
            $list_data = ArrayHelper::map(Client::find()->justActive()->all(), 'id', 'title');
        }

        $_items = [
            'Status' => [
                self::STATUS_ACTIVE => Yii::t("app", "Status Active"),
                self::STATUS_CLOSE => Yii::t("app", "Status Close"),
                self::STATUS_DOING => Yii::t("app", "Status Doing"),
            ],
            'CssStatus' => [
                self::STATUS_CLOSE => 'danger',
                self::STATUS_ACTIVE => 'success',
                self::STATUS_DOING => 'info',
            ],
            'Priority' => [
                self::PRIORITY_ESSENTIAL => 'ضروری',
                self::PRIORITY_HIGH => 'مهم',
                self::PRIORITY_MEDIUM => 'عادی',
                self::PRIORITY_LOW => 'پایین',
            ],
            'PriorityClass' => [
                self::PRIORITY_ESSENTIAL => 'danger',
                self::PRIORITY_HIGH => 'warning',
                self::PRIORITY_MEDIUM => 'success',
                self::PRIORITY_LOW => 'info',
            ],
            'ClassNameFilter' => [
                (TicketModule::getInstance()->comfortItemsClass) => 'امکانات رفاهی',
            ],
            'Notif' => [
                self::NOTIF_TICKET_SEND => 'تیکت جدید',
                self::NOTIF_TICKET_SEND_SUPPORT => 'تیکت پشتیبانی',
            ],
            'List' => $list_data,
            'Owner' => $list_data,
            'ClientList' => $list_data,
        ];

        if (isset($code))
            return isset($_items[$type][$code]) ? $_items[$type][$code] : false;
        else
            return isset($_items[$type]) ? $_items[$type] : false;
    }

    /**
     * @inheritdoc
     * @return TicketsQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new TicketsQuery(get_called_class());
        if(TicketModule::getInstance()->hasSlaves && !Yii::$app->client->isMaster()){
            $query->bySlave();
        }
        return $query->active();
    }

    protected function canDelete()
    {
        return true;
    }

    public function canChangeStatus(): bool
    {
        $firstTicket = self::findOne($this->parent_id ?: $this->id);
        $lastTicket = self::find()->andWhere([
            'parent_id' => $this->parent_id ?: $this->id,
            'kind' => self::KIND_REFER
        ])->orderBy([ 'id'=> SORT_DESC ])
            ->limit(1)
            ->one();
        $lastTicket = $lastTicket ?: $firstTicket;

        $currentUserId = Yii::$app->user->id;

        return $firstTicket?->creator_id == $currentUserId || in_array($currentUserId, array_map(fn($user) => $user->id, (array_merge($lastTicket?->users ?: [], $lastTicket?->department?->users ?: []))));
    }

    /**
     * @return bool
     */
    public function canAnswer()
    {
        if ($this->creator_id == 0) {
            return false;
        }
        if ($this->creator_id == Yii::$app->user->id) {
            return false;
        }
        return true;
    }

    /*
    * حذف منطقی
    */
    public function softDelete()
    {
        $this->status = self::STATUS_DELETED;
        if ($this->canDelete() && $this->save()) {
            return true;
        } else {
            return false;
        }
    }

    /*
    * فعال کردن
    */
    public function restore()
    {
        $this->status = self::STATUS_ACTIVE;
        if ($this->save()) {
            return true;
        } else {
            return false;
        }
    }

    public static function Show()
    {
        $views = TicketsView::find()->select('comment_id')->andWhere(['user_id' => Yii::$app->user->id]);
        return self::find()->andWhere(['NOT IN', 'id', $views])->all();
    }

    public function getViewed()
    {
        $a = $this->getCommentsViews()->andWhere(['user_id' => Yii::$app->user->id, 'viewed' => '1'])->createCommand()->rawSql;
        return $this->getCommentsViews()->andWhere(['user_id' => Yii::$app->user->id, 'viewed' => '1'])->exists();
    }

    public function setViewed()
    {
        if(!$viewModel = $this->getCommentsViews()->andWhere(['user_id' => Yii::$app->user->id])->one()){
            $viewModel = new TicketsView([
                'comment_id' => $this->id,
                'user_id' => Yii::$app->user->id
            ]);
        }
        $viewModel->viewed = 1;
        return $viewModel->save();
    }

    public function getOwnerList($total = true)
    {
        $list = '';
        $count = 0;
        $comment = Tickets::find()->where([
            'or',
            [
                'parent_id' => $this->parent_id ?: $this->id
            ],
            [
                'id' => $this->id
            ]
        ])->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->one();
        $owners = TicketsView::find()->joinWith(['user'])->andWhere(['comment_id' => $comment?->id])->all();
        foreach ($owners as $owner) {
            $user = $owner->user;
            if ($user) {
                if ($count > 0 && !$total) {
                    $list .= '<small class="badge-inverse py-1 px-2">...</small>';
                    break;
                }
                $count++;
                if ($owner->viewed == 1) {
                    $list .= '<small class="badge-inverse py-1 px-2">' . $user->fullName . '</small>';
                } else {
                    $list .= '<small class="badge-warning py-1 px-2">' . $user->fullName . '</small>';
                }
            }
        }
        return '<div class="d-inline-flex flex-wrap gap-1">' . $list . '</div>';
    }

    public function saveInbox()
    {
        if (TicketModule::getInstance()->hasSlaves && $this->type == self::TYPE_MASTER && $this->parent) {
                $model = new TicketsView();
                $model->user_id = $this->parent->creator_id;
                $model->comment_id = $this->id;
                $model->slave_id = $this->slave_id;
                if (!$model->save()) {
                    $this->addError('error_msg', Html::errorSummary($model));
                    return false;
                }
        }
        return true;
    }

    public static function countInbox($status = self::STATUS_ACTIVE, $exist = false)
    {
        $query = Tickets::find()
            ->joinWith(['commentsViews', 'department.usersPivot'])
            ->andWhere([
                'OR',
                ['AND', ['IS NOT', TicketsView::tableName() . '.user_id', null], [TicketsView::tableName() . '.user_id' => Yii::$app->user->id], [TicketsView::tableName() . '.viewed' => 0]],
                [TicketsView::tableName() . '.user_id' => null, TicketsDepartmentUsers::tableName() . '.user_id' => Yii::$app->user->id],
            ])
            ->andWhere(['<>', Tickets::tableName() . '.creator_id', Yii::$app->user->id])
            ->andWhere(['AND', [Tickets::tableName() . '.status' => $status]]);
        return $exist ? $query->exists() : $query->count();
    }

    public function hasOwnerByID($user_id): bool
    {
        return Tickets::find()->byComment($this->id)->byUser($user_id)->exists();
    }

    public function fields()
    {
        $fields = parent::fields();
        unset(
            $fields['parent_id'],
            $fields['creator_id'],
            $fields['update_id'],
            $fields['type'],
            //$fields['class_name'],
            //$fields['class_id'],
            $fields['link'],
            $fields['title'],
            $fields['status'],
            //$fields['due_date'],
            $fields['created'],
            $fields['changed']
        );

        $fields['color'] = function ($model) {
            return self::itemAlias('Type', $model->priority);
        };

        return $fields;
    }

    public function getFullTitle()
    {
        if ($this->type_task) {
            return $this->typeTask->title . ' - ' . $this->title;
        } else {
            return $this->title;
        }
    }

    /**
     * @return string
     */
    public function getHtmlLink()
    {
        if ($this->link) {
            if ($this->typeTask?->is_auto) {
                return Html::a(Yii::t("app", "More Info"), $this->link, ['class' => 'btn btn-info']);
            } else {
                return Html::a(Yii::t("app", "More Info"), [$this->link, 'id' => $this->class_id], ['class' => 'btn btn-info']);
            }
        }
        return '';
    }

    /**
     * @return string
     */
    public function getSender()
    {
        return $this->creator ? $this->creator->fullName : 'سیستم';
    }

    /**
     * @param $type
     * @param $title
     * @param $content
     * @param $link
     * @param array $users
     * @return void
     */
    public static function sendAuto(
        $type,
        $title,
        $content,
        $link,
        array $users = [],
        $className = null,
        $classId = null
    )
    {
        $commentTypes = CommentsType::find()->andWhere(['key' => $type])->all();
        $parentId = self::find()
            ->select(['parent_id'])
            ->andWhere(['class_name' => $className, 'class_id' => $classId])
            ->andWhere(['<>' , 'parent_id', 0])
            ->limit(1)
            ->one()?->parent_id;

        foreach ($commentTypes as $commentType) {
            $model = new self(['scenario' => self::SCENARIO_AUTO]);
            $model->type = self::TYPE_PRIVATE;
            $model->kind = self::KIND_TICKET;
            $model->priority = self::PRIORITY_MEDIUM;
            $model->type_task = $commentType->id;
            $model->title = $title;
            $model->des = $content;
            $model->link = $link;
            $model->class_name = $className;
            $model->class_id = $classId;
            $model->parent_id = $parentId ?: 0;
            $model->owner = $commentType->userType === CommentsType::USER_STATIC ? $users : $commentType->users;
            $model->useTitleInNotification = true;

            if (count($model->owner) > 0) {
                $model->send_sms = $commentType->sendSms;
                $model->send_email = $commentType->sendMail;
                if ($commentType->is_auto) {
                    $model->save(false) && $model->saveInbox();
                } else {
                    $model->useDescriptionInNotification = true;
                    $model->generateDescription();
                    $model->sendNotifications();
                }
            }
        }
    }

    public function notificationTitle(): string
    {
        return !$this->id || $this->useTitleInNotification ? $this->title : 'تیکت جدید';
    }

    public function notificationCustomDescription(): ?string
    {
        if ($this->useDescriptionInNotification) {
            return $this->des;
        }

        $url = Yii::$app->urlManager->createAbsoluteUrl(['ticket/inbox']);
        if ($this->send_sms) {
            return 'تیکت جدید' . PHP_EOL . 'برای رفتن به صندوق ورودی تیکت ها روی لینک زیر کلیک کنید.' . PHP_EOL . $url;
        } else {
            return ($this->des ?  $this->des . '<br /><br />' : '') . '<a class="text-info" href="' . $url . '">رفتن به صندوق ورودی تیکت ها</a>';
        }
    }

    public function notificationConditionToSend(): bool
    {
        return true;
    }

    public function notificationSmsConditionToSend(): bool
    {
        return (bool)$this->send_sms;
    }

    public function notificationEmailConditionToSend(): bool
    {
        return (bool)$this->send_email;
    }

    public function notificationEmailDelayToSend(): ?int
    {
        return $this->send_email_at ? $this->send_email_at - time() : 0;
    }

    public function notificationSmsDelayToSend(): ?int
    {
        return $this->send_sms_at ? $this->send_sms_at - time() : 0;
    }

    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->created = time();
            if ($this->getScenario() == self::SCENARIO_AUTO) {
                $this->creator_id = 0;
            } else {
                $this->creator_id = !Yii::$app->user->isGuest ? Yii::$app->user->id : 0;
            }
            $this->status = self::STATUS_ACTIVE;
            if(TicketModule::getInstance()->hasSlaves){
                $this->slave_id = $this->slave_id ?: Yii::$app->client->id;
            }
            if($this->type == self::TYPE_MASTER){
                $this->user_fullName = Yii::$app->user->identity->fullName;
                $this->user_number = Yii::$app->user->identity->username;
            }
        }
        $this->des = !empty(\Yii::$app->phpNewVer->trim($this->des)) ? HtmlPurifier::process($this->des) : NULL;
        $this->update_id = !Yii::$app->user->isGuest ? Yii::$app->user->id : 0;
        $this->changed = time();
        return parent::beforeSave($insert);
    }

    /**
     * Determine if viewer if author of the comment
     *
     * @return bool
     */
    public function getViewerIsAuthor(): bool
    {
        return Yii::$app->user->identity->id == $this->creator_id;
    }

    /**
     * Get messages of the thread
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMessages()
    {
        return Tickets::find()
            ->where(['id' => $this->id])
            ->orWhere(['parent_id' => $this->parent_id ?: $this->id])
            ->orWhere(['id' => $this->parent_id]);
    }

    /**
     * Get thread latest message
     *
     * @return Tickets|null
     */
    public function getLatestMessage(): Tickets|null
    {
        return $this->getMessages()
            ->orderBy(['created' => SORT_DESC])
            ->limit(1)
            ->one();
    }

    public function notifUsers(string $event): array
    {
        // If a user is assigned, return their ID.
        if ($this->assignedTo) {
            return [$this->assignedTo->id];
        }

        // Map department users once for reuse.
        $departmentUsers = $this->department ? ArrayHelper::map($this->department->users, 'id', 'id') : [];

        // If not a master ticket, return department users.
        if ($this->type !== self::TYPE_MASTER) {
            return $departmentUsers;
        }

        // Check if it is a master ticket with slave tickets enabled.
        $hasSlaves = TicketModule::getInstance()->hasSlaves;

        // Master client logic.
        if ($this->type === self::TYPE_MASTER && $hasSlaves) {
            if (Yii::$app->client->isMaster()) {
                return $departmentUsers;
            }

            if (!$this->department_id) {
                return [];
            }

            return $departmentUsers;
        }

        // Default case: no users to notify.
        return [];
    }

    public function notifTitle(string $event): string
    {
        return match ($this->getScenario()) {
            self::SCENARIO_SEND => 'تیکت جدید',
            default => '',
        };
    }

    public function notifLink(string $event, ?int $userId): ?string
    {
        return Yii::$app->urlManager->createAbsoluteUrl([TicketModule::createUrl('ticket/index', ['thread_id' => $this->id])]);
    }

    public function notifDescription(string $event): ?string
    {
        if ($this->scenario === self::SCENARIO_SEND) {
            return "یک پاسخ برای تیکت {$this->title} ثبت شد.";
        }

        return '';
    }

    public function notifConditionToSend(string $event): bool
    {
        return $this->send_notif;
    }

    public function notifSmsConditionToSend(string $event): bool
    {
        return true;
    }

    public function notifSmsDelayToSend(string $event): ?int
    {
        return 0;
    }

    public function notifEmailConditionToSend(string $event): bool
    {
        return true;
    }

    public function notifEmailDelayToSend(string $event): ?int
    {
        return 0;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if(TicketModule::getInstance()->hasSlaves){
            $sendNotifClientId = Yii::$app->client->id;
            if ($this->type == self::TYPE_MASTER){
                $clientComponentClass = TicketModule::getInstance()->clientComponentClass;
                $sendNotifClientId = Yii::$app->client->isMaster() ? $this->slave_id : $clientComponentClass::getMasterClient()->id;
            }
            Yii::$app->queue->push(new SendTicketNotifJob([
                'slaveId' => $sendNotifClientId,
                'ticket_id' => $this->id,
            ]));
        }
    }
}
