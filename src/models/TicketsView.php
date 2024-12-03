<?php

namespace hesabro\ticket\models;

use hesabro\errorlog\behaviors\TraceBehavior;
use hesabro\ticket\TicketModule;
use Yii;

/**
 * This is the model class for table "{{%comments_view}}".
 *
 * @property string $user_id
 * @property int $comment_id
 * @property int $viewed
 * @property int $insert_date
 *
 * @property User $user
 * @property Tickets $ticket
 */
class TicketsView extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tickets_view}}';
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
        return [
            [['user_id', 'comment_id'], 'required'],
            [['user_id', 'comment_id', 'viewed', 'insert_date'], 'integer'],
            [['user_id', 'comment_id'], 'unique', 'targetAttribute' => ['user_id', 'comment_id']],
            //[['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Yii::$app->user->identityClass, 'targetAttribute' => ['user_id' => 'id']],
            [['comment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tickets::class, 'targetAttribute' => ['comment_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('app', 'User ID'),
            'comment_id' => Yii::t('app', 'Comment ID'),
            'insert_date' => Yii::t('app', 'Insert Date'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        $userModelClass = Yii::$app->user->identityClass;
        return $this->hasOne($userModelClass, ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComment()
    {
        return $this->hasOne(Tickets::class, ['id' => 'comment_id']);
    }

    /**
     * {@inheritdoc}
     * @return TicketsViewQuery() the active query used by this AR class.
     */
    public static function find()
    {
        $query = new TicketsViewQuery(get_called_class());
        if(TicketModule::getInstance()->hasSlaves){
            $query->bySlave();
        }
        return $query;
    }


    public function beforeSave($insert)
    {
        if(TicketModule::getInstance()->hasSlaves){
            $this->slave_id = $this->slave_id ?: Yii::$app->client->id;
        }
        return parent::beforeSave($insert);
    }

    public function behaviors()
    {
        return [
            [
                'class' => TraceBehavior::class,
                'ownerClassName' => self::class
            ],
        ];
    }
}
