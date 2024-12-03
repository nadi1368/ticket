<?php

namespace hesabro\ticket\models;

use Yii;

/**
 * This is the ActiveQuery class for [[CommentsView]].
 *
 * @see CommentsView
 */
class TicketsViewQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return TicketsView[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return TicketsView|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function bySlave(): void
    {
        $this->andOnCondition([TicketsView::tableName() . '.slave_id' => Yii::$app->client->id]);
    }

    public function byUser($user_id): self
    {
        return $this->andWhere(['user_id' => $user_id]);
    }

    public function byComment($comment_id): self
    {
        return $this->andWhere(['comment_id' => $comment_id]);
    }
}
