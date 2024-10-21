<?php

namespace hesabro\ticket\models;

use backend\models\User;
use hesabro\changelog\behaviors\LogBehavior;
use hesabro\errorlog\behaviors\TraceBehavior;
use hesabro\helpers\behaviors\JsonAdditional;
use hesabro\ticket\TicketModule;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%comments_type}}".
 *
 * @property string $id
 * @property string $creator_id
 * @property string $update_id
 * @property string $title
 * @property string $key
 * @property int $is_auto
 * @property string $status
 * @property string $created
 * @property string $changed
 *
 * @property Comments[] $comments
 * @property string $usersList
 */
class CommentsType extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 0;

    const ERROR_REPORT = 'ERROR_REPORT';
    const REQUEST_ADVANCE_MONEY = 'REQUEST_ADVANCE_MONEY';
    const REQUEST_ADVANCE_MONEY_REJECT = 'REQUEST_ADVANCE_MONEY_REJECT';
    const REQUEST_ADVANCE_MONEY_CONFIRM = 'REQUEST_ADVANCE_MONEY_CONFIRM';
    const REQUEST_LEAVE_REPORT = 'REQUEST_LEAVE_REPORT';
    const REQUEST_COMFORT = 'REQUEST_COMFORT_CREATE';
    const REQUEST_COMFORT_CONFIRM = 'REQUEST_COMFORT_CONFIRM';
    const REQUEST_COMFORT_REJECT = 'REQUEST_COMFORT_REJECT';
    const REQUEST_PERMISSION = 'REQUEST_PERMISSION';
    const EMPLOYEE_UPDATE_PROFILE = 'EMPLOYEE_UPDATE_PROFILE';
    const EMPLOYEE_UPDATE_PROFILE_REJECT = 'EMPLOYEE_UPDATE_PROFILE_REJECT';
    const LETTER_INTERNAL = 'LETTER_INTERNAL';
    const LETTER_OUTPUT = 'LETTER_INPUT';

    const SCENARIO_CREATE = 'create';

    const USER_DYNAMIC = 'user_dynamic';
    const USER_STATIC = 'user_static';

    /**  AdditionalData */
    public $description = null;

    public $users = [];

    public $sendSms = false;

    public $sendMail = false;

    public $userType = 'user_dynamic';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tickets_type}}';
    }

    public static function getDb()
    {
        return Yii::$app->get(TicketModule::getInstance()->db);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $userDynamicType = self::USER_DYNAMIC;
        return [
            [['title', 'key', 'userType'], 'required'],
            [['creator_id', 'update_id', 'status', 'created', 'changed', 'is_auto', 'sendSms', 'sendMail'], 'integer'],
            [['title', 'key'], 'string', 'max' => 64],
            [['key'], 'match', 'pattern' => '/^[a-zA-Z][\w_-]*$/'],
            [['description'], 'string'],
            [['users'], 'filter', 'filter' => function ($value) {
                return (is_array($value) ? $value : []);
            }],
            ['key', 'in', 'range' => array_keys(self::itemAlias('KeyType'))],
            ['userType', 'in', 'range' => [self::USER_DYNAMIC, self::USER_STATIC]],
            [
                'users',
                'required',
                'when' => fn(self $model) => $model->userType === self::USER_DYNAMIC,
                'whenClient' => "function() { return $('input[name=\"CommentsType[userType]\"]:checked').val() === '$userDynamicType' }"
            ]
        ];
    }

    public function beforeValidate()
    {
        if ($this->userType === self::USER_STATIC) {
            $this->users = [];
        }

        return parent::beforeValidate();
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios[self::SCENARIO_CREATE] = ['title', 'key', 'is_auto', 'description', 'userType', 'users', 'sendSms', 'sendMail'];

        return $scenarios;
    }

    public function validateUser($attribute, $params)
    {
        if (!$this->hasErrors()) {
            foreach ($this->users as $userId) {
                if (($user = User::findOne($userId)) !== null && empty($user->email)) {
                    $this->addError('users', "کاربر {$user->fullName}  آدرس ایمیل ندارد.");
                    break;
                }
            }
        }
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
            'title' => Yii::t('app', 'Title'),
            'status' => Yii::t('app', 'Status'),
            'created' => Yii::t('app', 'Created'),
            'changed' => Yii::t('app', 'Changed'),
            'is_auto' => Yii::t('app', 'Ticket'),
            'description' => Yii::t('app', 'Description'),
            'users' => Yii::t('app', 'Users'),
            'key' => Yii::t('app', 'Event'),
            'sendSms' => 'ارسال پیامک',
            'sendMail' => 'ارسال ایمیل',
            'userType' => Yii::t('app', 'User Type')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comments::className(), ['type_task' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreator()
    {
        $userModelClass = Yii::$app->user->identityClass;
        return $this->hasOne($userModelClass, ['id' => 'creator_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdate()
    {
        $userModelClass = Yii::$app->user->identityClass;
        return $this->hasOne($userModelClass, ['id' => 'update_id']);
    }

    /**
     * @return string
     */
    public function getUsersList(): string
    {
        $list = '';
        foreach (is_array($this->users) ? $this->users : [] as $userId) {
            /** @var User $user */
            if (($user = User::findOne($userId)) !== null) {
                $list .= '<label class="badge badge-info mr-2 mb-2">' . $user->fullName . ' (' . $user->email . ')' . ' </label> ';
            }
        }
        return $list;
    }

    /**
     * {@inheritdoc}
     * @return CommentsTypeQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new CommentsTypeQuery(get_called_class());
        return $query->active();
    }

    public function canUpdate()
    {
        return true;
    }

    public function canDelete()
    {
        if ($this->is_auto) {
            return false;
        }
        return Comments::find()->andWhere(['type_task' => $this->id])->limit(1)->one() === null;
    }

    /*
    * حذف منطقی
    */
    public function softDelete()
    {
        $this->status = self::STATUS_DELETED;
        return $this->save(false);
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

    public static function itemAlias($type, $code = null)
    {
        $listData = [];
        if ($type == 'List') {
            $list = self::find()->all();
            $listData = ArrayHelper::map($list, 'id', 'title');
        }
        if ($type == 'ListManual') {
            $list = self::find()->manual()->all();
            $listData = ArrayHelper::map($list, 'id', 'title');
        }

        $items = [
            'List' => $listData,
            'ListManual' => $listData,
            'KeyType' => [
                self::ERROR_REPORT => 'خطا سیستم',
                self::REQUEST_ADVANCE_MONEY => 'ثبت درخواست مساعده',
                self::REQUEST_ADVANCE_MONEY_REJECT => 'رد درخواست مساعده',
                self::REQUEST_ADVANCE_MONEY_CONFIRM => 'تایید درخواست مساعده',
                self::REQUEST_LEAVE_REPORT => 'ثبت درخواست مرخصی',
                self::REQUEST_COMFORT => 'درخواست امکانات رفاهی',
                self::REQUEST_COMFORT_CONFIRM => 'تایید امکانات رفاهی',
                self::REQUEST_COMFORT_REJECT => 'رد امکانات رفاهی',
                self::REQUEST_PERMISSION => 'درخواست دسترسی',
                self::EMPLOYEE_UPDATE_PROFILE => 'درخواست ویرایش حساب کاربری',
                self::EMPLOYEE_UPDATE_PROFILE_REJECT => 'رد درخواست ویرایش حساب کاربری',
                self::LETTER_INTERNAL => 'نامه داخلی',
                self::LETTER_OUTPUT => 'نامه وارده بین سیستمی',
            ],
            'UserType' => [
                self::USER_DYNAMIC => 'کاربر دلخواه',
                self::USER_STATIC => 'کاربر رخداد'
            ]
        ];

        return isset($code) ? ($items[$type][$code] ?? false) : ($items[$type] ?? false);
    }

    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->created = time();
            $this->creator_id = Yii::$app->user->id;
            $this->status = self::STATUS_ACTIVE;
        }
        $this->update_id = Yii::$app->user->id;
        $this->changed = time();
        return parent::beforeSave($insert);
    }

    public static function cacheDependency()
    {
        return new TagDependency(['tags' => 'comments-type']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        TagDependency::invalidate(Yii::$app->cache, ['comments-type']);

        parent::afterSave($insert, $changedAttributes);
    }

    public function behaviors()
    {
        return [
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
                    'description' => 'String',
                    'users' => 'Array',
                    'sendSms' => 'Boolean',
                    'sendMail' => 'Boolean',
                    'userType' => 'String',
                ]
            ],
        ];
    }

}
