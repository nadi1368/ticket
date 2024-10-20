<?php

namespace hesabro\ticket\models;


use hesabro\helpers\components\Helper;

/**
 * This is the ActiveQuery class for [[CommentsType]].
 *
 * @see CommentsType
 */
class CommentsTypeQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return CommentsType[]|array
     */
    public function all($db = null)
    {
        $this->cache(0, CommentsType::cacheDependency());
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return CommentsType|array|null
     */
    public function one($db = null)
    {
        $this->cache(0, CommentsType::cacheDependency());
        return parent::one($db);
    }

    public function active()
    {
        return $this->onCondition(['<>', CommentsType::tableName() . '.status', CommentsType::STATUS_DELETED]);
    }

    public function manual()
    {
        return $this->andWhere(['<>', 'is_auto', Helper::YES]);
    }


    public function byKey(string $key)
    {
        return $this->andWhere(['key'=>$key])->limit(1);
    }
}
