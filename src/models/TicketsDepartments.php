<?php

namespace hesabro\ticket\models;

use common\components\Client;
use hesabro\helpers\behaviors\StatusActiveBehavior;
use hesabro\ticket\TicketModule;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%tickets_departments}}".
 *
 * @property int $id
 * @property string $title
 * @property int|null $status
 * @property int $created_at
 * @property int|null $created_by
 * @property int $updated_at
 * @property int|null $updated_by
 * @property int $slave_id
 *
 * @property User[] $users
 */
class TicketsDepartments extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 0;

	const SCENARIO_CREATE = 'create';

    public $user_ids;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tickets_departments}}';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get(TicketModule::getInstance()->db);
    }

	public function behaviors()
	{
		return [
			[
				'class' => TimestampBehavior::class,
			],
			[
				'class' => BlameableBehavior::class,
			],
            [
                'class' => StatusActiveBehavior::class
            ]
		];
	}

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'user_ids'], 'required'],
            [['status', 'created_at', 'created_by', 'updated_at', 'updated_by', 'slave_id'], 'integer'],
            [['title'], 'string', 'max' => 64],
            [['user_ids'], 'each', 'rule' => ['required']],
            [['user_ids'], 'each', 'rule' => ['exist', 'skipOnError' => true, 'targetClass' => Yii::$app->user->identityClass, 'targetAttribute' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('tickets', 'ID'),
            'title' => Yii::t('tickets', 'Title'),
            'status' => Yii::t('tickets', 'Status'),
            'created_at' => Yii::t('tickets', 'Created At'),
            'created_by' => Yii::t('tickets', 'Created By'),
            'updated_at' => Yii::t('tickets', 'Updated At'),
            'updated_by' => Yii::t('tickets', 'Updated By'),
            'user_ids' => Yii::t('tickets', 'Users'),
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios[self::SCENARIO_CREATE] = ['id', 'title', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by', 'slave_id', ];

        return $scenarios;
    }

    /**
    * @return \yii\db\ActiveQuery
    */
	public function getCreator()
	{
        $userModelClass = Yii::$app->user->identityClass;
		return $this->hasOne($userModelClass, ['id' => 'created_by']);
	}

	/**
	* @return \yii\db\ActiveQuery
	*/
	public function getUpdate()
	{
        $userModelClass = Yii::$app->user->identityClass;
		return $this->hasOne($userModelClass, ['id' => 'updated_by']);
	}

    public function getUsersPivot(): ActiveQuery
    {
        return $this->hasMany(TicketsDepartmentUsers::class, ['department_id' => 'id']);
    }

    public function getUsers(): ActiveQuery
    {
        $userModelClass = Yii::$app->user->identityClass;
        return $this->hasMany($userModelClass, ['id' => 'user_id'])
            ->via('usersPivot');
    }

    /**
     * {@inheritdoc}
     * @return TicketsDepartmentsQuery the active query used by this AR class.
     */
    public static function find($slave_id = null)
    {
        $query = new TicketsDepartmentsQuery(get_called_class());
        if(TicketModule::getInstance()->hasSlaves){
            $query->bySlave($slave_id);
        }
        return $query->active();
    }

    public function canUpdate()
    {
        return true;
    }

    public function canDelete()
    {
        return true;
    }
    /*
    * حذف منطقی
    */
    public function softDelete()
    {
		if($this->canDelete()){
			$this->status = self::STATUS_DELETED;
			if ($this->save()) {
				return true;
			}
		}
		return false;
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

    public static function itemAlias($type, $code = null, $returnType = 'object')
    {
        $items = match ($type) {
            'Status' => [
                self::STATUS_ACTIVE => Yii::t('tickets', 'Status Active'),
                self::STATUS_DELETED => Yii::t('tickets', 'Status Delete'),
            ],
            'List' => ArrayHelper::map(self::find()->all(), 'id', 'title'),
            'MasterList' => ArrayHelper::map(self::find(Client::getMasterClient()->id)->all(), 'id', 'title'),
            default => false
        };

        $items = $items instanceof \Closure ? $items() : $items;
        if (isset($code)) {
            return $items[$code] ?? false;
        } else {
            return $items ? $returnType == 'object' ? $items : Helper::convertObjectToArray($items) : false;
        }
    }

    public function beforeSave($insert)
    {
        if($this->isNewRecord){
            if(TicketModule::getInstance()->hasSlaves){
                $this->slave_id = $this->slave_id ?: Yii::$app->client->id;
            }
        }
        return parent::beforeSave($insert);
    }
}
