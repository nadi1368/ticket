<?php

namespace hesabro\ticket\models;

use Yii;

/**
 * This is the model class for table "{{%tickets_department_users}}".
 *
 * @property int $id
 * @property int|null $department_id
 * @property int|null $user_id
 * @property int $created_at
 * @property int|null $created_by
 * @property int $updated_at
 * @property int|null $updated_by
 */
class TicketsDepartmentUsers extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tickets_department_users}}';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('master');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['department_id', 'user_id'], 'integer'],
            [['created_at', 'updated_at'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('tickets', 'ID'),
            'department_id' => Yii::t('tickets', 'Department ID'),
            'user_id' => Yii::t('tickets', 'User ID'),
            'created_at' => Yii::t('tickets', 'Created At'),
            'created_by' => Yii::t('tickets', 'Created By'),
            'updated_at' => Yii::t('tickets', 'Updated At'),
            'updated_by' => Yii::t('tickets', 'Updated By'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return TicketsDepartmentUsersQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TicketsDepartmentUsersQuery(get_called_class());
    }
}
