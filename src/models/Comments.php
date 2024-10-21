<?php

namespace hesabro\ticket\models;

use hesabro\changelog\behaviors\LogBehavior;
use hesabro\errorlog\behaviors\TraceBehavior;
use hesabro\helpers\behaviors\JsonAdditional;
use hesabro\helpers\components\Jdf;
use hesabro\helpers\validators\DateValidator;
use hesabro\ticket\TicketModule;
use mamadali\S3Storage\behaviors\StorageUploadBehavior;
use mamadali\S3Storage\components\S3Storage;
use managerBranch\models\CommentsViewMaster;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%comments}}".
 *
 * @mixin StorageUploadBehavior
 * @mixin NotificationBehavior
 *
 * @property int $id
 * @property int $parent_id
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
 * @property int $css_class
 * @property int $status
 * @property string $due_date
 * @property string $created
 * @property string $changed
 * @property string $file_name
 * @property string $additional_data
 *
 * @property CommentsView[] $commentsViews
 * @property User[] $users
 * @property User[] $owners
 * @property User $creator
 * @property CommentsType $typeTask
 * @property string $fullTitle
 * @property string $htmlLink
 * @property string $sender
 */
class Comments extends \yii\db\ActiveRecord
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DOING = 2;
    const STATUS_CLOSE = 3;

    const TYPE_DANGER = 1;
    const TYPE_WARNING = 2;
    const TYPE_SUCCESS = 3;
    const TYPE_INFO = 4;

    const TYPE_PUBLIC = 0;
    const TYPE_PRIVATE = 1;

    const TYPE_MASTER = 2;

    const KIND_TICKET = 1;

    const KIND_REFER = 2;

    const KIND_THREAD = 3;

    const SCENARIO_CREATE = 'create';
    const SCENARIO_CREATE_APP = 'create_app';
    const SCENARIO_SEND = 'send';
    const SCENARIO_ANSWER = 'answer';
    const SCENARIO_AUTO = 'auto';
    const SCENARIO_REPORT_BUG = 'report_bug';
    const SCENARIO_MASTER = 'master-ticket';

    const SCENARIO_REFER = 'refer';

    const MASTER_TASK_TYPE_FINANCE = 1;
    const MASTER_TASK_TYPE_TECHNICAL = 2;

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
                'scenarios' => [self::SCENARIO_CREATE, self::SCENARIO_MASTER, self::SCENARIO_CREATE_APP, self::SCENARIO_SEND],
                'path' => 'comments/{id}',
                'sharedWith' => function (self $model) {
                    $clientComponentClass = TicketModule::getInstance()->clientComponentClass;
                    if($clientComponentClass){
                        return $model->type == self::TYPE_MASTER ? [$clientComponentClass::getMasterClient()->id] : [];
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
                'fieldAdditional' => 'additional_data',
                'AdditionalDataProperty' => [
                    'master_task_type_id' => 'NullInteger',
                    'referrer_url' => 'String',
                    'is_duty' => 'Boolean',
                    'direct_parent_id' => 'NullInteger'
                ],

            ],
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
            [['des', 'css_class', 'title'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_SEND]],
            [['des', 'master_task_type_id', 'css_class', 'title'], 'required', 'on' => [self::SCENARIO_MASTER]],
            [['des', 'css_class', 'class_id', 'due_date'], 'required', 'on' => [self::SCENARIO_CREATE_APP]],
            [['des', 'css_class'], 'required', 'on' => [self::SCENARIO_REPORT_BUG]],
            [['owner'], 'required', 'on' => [self::SCENARIO_SEND, self::SCENARIO_REFER]],
            [['owner', 'type_task'], 'required', 'on' => [self::SCENARIO_AUTO]],
            [[
                'parent_id', 'type', 'creator_id', 'update_id', 'class_id', 'css_class', 'status', 'created', 'changed',
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
            [['type_task'], 'exist', 'skipOnError' => true, 'targetClass' => CommentsType::class, 'targetAttribute' => ['type_task' => 'id']],
            [['send_email_at', 'send_sms_at'], 'number', 'enableClientValidation' => false],
            [['send_email_at', 'send_sms_at'], 'validateTimeAfter'],
        ];
    }

    public function validateRefer($attribute): void
    {
        if ($this->kind === self::KIND_THREAD) {
            $this->addError($attribute, 'امکان ارجاع این تیکت وجود ندارد.');
        }

        $parent = $this->direct_parent_id ? Comments::findOne($this->direct_parent_id) : null;
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
        $scenarios[self::SCENARIO_CREATE] = ['des', 'css_class', 'title', 'owner', 'parent_id', 'creator_id', 'update_id', 'class_id', 'status', 'created', 'changed', 'due_date', 'class_name', 'link', 'file'];
        $scenarios[self::SCENARIO_MASTER] = ['des', 'title', 'css_class', 'owner', 'parent_id', 'creator_id', 'update_id', 'due_date', 'link', 'file', 'master_task_type_id'];
        $scenarios[self::SCENARIO_CREATE_APP] = ['des', 'css_class', 'title', 'owner', 'parent_id', 'creator_id', 'update_id', 'class_id', 'status', 'due_date', 'class_name', 'link', 'file'];
        $scenarios[self::SCENARIO_SEND] = ['des', 'css_class', 'title', 'owner', 'parent_id', 'creator_id', 'update_id', 'class_id', 'status', 'created', 'changed', 'due_date', 'class_name', 'link', 'file', 'send_sms', 'send_email', 'send_sms_at', 'send_email_at'];
        $scenarios[self::SCENARIO_AUTO] = ['des', 'css_class', 'title', 'owner', 'parent_id', 'creator_id', 'update_id', 'class_id', 'status', 'created', 'changed', 'due_date', 'class_name', 'link', 'send_sms', 'type_task'];
        $scenarios[self::SCENARIO_ANSWER] = ['des', 'css_class', 'title', 'owner', 'parent_id', 'creator_id', 'update_id', 'class_id', 'status', 'created', 'changed', 'due_date', 'class_name', 'link', 'send_sms', 'type_task', 'send_sms', 'send_email', 'send_sms_at', 'send_email_at'];
        $scenarios[self::SCENARIO_REPORT_BUG] = ['des', 'css_class'];
        $scenarios[self::SCENARIO_REFER] = ['des', 'owner', 'send_sms', 'send_email', 'send_sms_at', 'send_email_at'];

        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'creator_id' => Yii::t('app', 'Creator ID'),
            'update_id' => Yii::t('app', 'Update ID'),
            'owner' => Yii::t('app', 'Receivers'),
            'type_task' => Yii::t('app', 'Type') . ' ' . Yii::t('app', 'Task'),
            'class_name' => Yii::t('app', 'Related To'),
            'class_id' => Yii::t('app', 'Class ID'),
            'title' => Yii::t('app', 'Title'),
            'des' => Yii::t('app', 'Ticket Description'),
            'css_class' => Yii::t('app', 'Priority'),
            'send_sms' => Yii::t('app', 'Send Sms'),
            'send_email' => Yii::t('app', 'Send Email'),
            'status' => Yii::t('app', 'Status'),
            'due_date' => Yii::t('app', 'Due Done'),
            'created' => Yii::t('app', 'Created'),
            'changed' => Yii::t('app', 'Changed'),
            'file' => Yii::t('app', 'Attach File'),
            'master_task_type_id' => Yii::t('app', 'بخش مربوطه'),
            'is_duty' => Yii::t('app', 'Duty'),
            'send_email_at' => Yii::t('app', 'Send Email Date'),
            'send_sms_at' => Yii::t('app', 'Send Sms Date'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCommentsViews()
    {
        return $this->hasMany(CommentsView::class, ['comment_id' => 'id']);
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
            $userClass = Yii::$app->user->identityClass;
            $users = (TicketModule::getInstance()->authAssignmentClass)::find()->select('user_id')->andWhere(['IN', 'item_name', (TicketModule::getInstance()->authItemChildClass)::find()->select('parent')->andWhere(['child' => TicketModule::getInstance()->getCommentsPermission])]);
            $user = $userClass::find()->andWhere(['IN', 'id', $users])->andWhere(['!=', 'id', Yii::$app->user->id])->all();
            $list_data = ArrayHelper::map($user, 'id', 'fullName');
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
            'Type' => [
                self::TYPE_DANGER => 'ضروری',
                self::TYPE_WARNING => 'مهم',
                self::TYPE_SUCCESS => 'عادی',
                self::TYPE_INFO => 'پایین',
            ],
            'CssClass' => [
                self::TYPE_DANGER => 'danger',
                self::TYPE_WARNING => 'warning',
                self::TYPE_SUCCESS => 'success',
                self::TYPE_INFO => 'info',
            ],
            'MasterTaskType' => [
                self::MASTER_TASK_TYPE_FINANCE => 'بخش مالی',
                self::MASTER_TASK_TYPE_TECHNICAL => 'بخش فنی',
            ],
            'ClassNameFilter' => [
                (TicketModule::getInstance()->comfortItemsClass) => 'امکانات رفاهی',
            ],
            'List' => $list_data,
            'Owner' => $list_data,
        ];

        if (isset($code))
            return isset($_items[$type][$code]) ? $_items[$type][$code] : false;
        else
            return isset($_items[$type]) ? $_items[$type] : false;
    }

    /**
     * @inheritdoc
     * @return CommentsQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new CommentsQuery(get_called_class());
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

        return $firstTicket?->creator_id == $currentUserId || in_array($currentUserId, array_map(fn($user) => $user->id, $lastTicket?->users ?: []));
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
        $views = CommentsView::find()->select('comment_id')->andWhere(['user_id' => Yii::$app->user->id]);
        return self::find()->andWhere(['NOT IN', 'id', $views])->all();
    }

    public function getViewed()
    {
        return $this->getCommentsViews()->andWhere(['comment_id' => $this->id, 'user_id' => Yii::$app->user->id, 'viewed' => '0'])->count();
    }

    public function setViewed()
    {
        CommentsView::updateAll(['viewed' => 1, 'insert_date' => time()], ['user_id' => Yii::$app->user->id, 'comment_id' => [$this->id, $this->parent_id], 'viewed' => 0]);
    }

    public function getOwnerList($total = true)
    {
        $list = '';
        $count = 0;
        $comment = Comments::find()->where([
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
        $owners = CommentsView::find()->joinWith(['user'])->andWhere(['comment_id' => $comment?->id])->all();
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

    public function saveInbox($master = false)
    {
        if ($this->type == self::TYPE_PRIVATE) {
            foreach ($this->owner as $item) {
                $commentViewMasterClass = TicketModule::getInstance()->commentsViewMasterClass;
                $model = $master ? new $commentViewMasterClass() : new CommentsView();
                $model->user_id = $item;
                $model->comment_id = $this->id;
                if ($master) $model->slave_id = $this->slave_id;
                if (!$model->save()) {
                    $this->addError('error_msg', Html::errorSummary($model));
                    return false;
                }
            }
        }
        return true;
    }

    public static function countInbox($status = self::STATUS_ACTIVE, $exist = false)
    {
        $query = CommentsView::find()
            ->joinWith(['comment'])
            ->andWhere(['user_id' => Yii::$app->user->id, 'viewed' => 0])
            ->andWhere(['<>', 'creator_id', Yii::$app->user->id])
            ->andWhere(['AND', [Comments::tableName() . '.status' => $status]]);

        return $exist ? $query->exists() : $query->count();
    }

    public static function countInboxMaster($status = self::STATUS_ACTIVE)
    {
        $commentsMasterClass = TicketModule::getInstance()->commentsMasterClass;
        return $commentsMasterClass ? $commentsMasterClass::find()
            ->andWhere(['type' => self::TYPE_MASTER])
            ->andWhere([Comments::tableName() . '.status' => $status])
            ->count() : 0;
    }

    public function hasOwnerByID($user_id): bool
    {
        return CommentsView::find()->byComment($this->id)->byUser($user_id)->exists();
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
            //$fields['css_class'],
            $fields['status'],
            //$fields['due_date'],
            $fields['created'],
            $fields['changed']
        );

        $fields['color'] = function ($model) {
            return self::itemAlias('Type', $model->css_class);
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
            $model->css_class = self::TYPE_SUCCESS;
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

    protected function notifySupporter(): void
    {
        TicketModule::getInstance()->notifySupporter($this);
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
        }
        $this->des = !empty(\Yii::$app->phpNewVer->trim($this->des)) ? HtmlPurifier::process($this->des) : NULL;
        $this->update_id = !Yii::$app->user->isGuest ? Yii::$app->user->id : 0;
        $this->changed = time();
        if ($this->type == self::TYPE_MASTER) {
            $this->notifySupporter();
        }
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
        return Comments::find()
            ->where(['id' => $this->id])
            ->orWhere(['parent_id' => $this->parent_id ?: $this->id])
            ->orWhere(['id' => $this->parent_id]);
    }

    /**
     * Get thread latest message
     *
     * @return Comments|null
     */
    public function getLatestMessage(): Comments|null
    {
        return $this->getMessages()
            ->orderBy(['created' => SORT_DESC])
            ->limit(1)
            ->one();
    }
}
