<?php

namespace hesabro\ticket\models;

/**
 * This is the ActiveQuery class for [[CommentsView]].
 *
 * @see CommentsView
 */
class CommentsViewQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return CommentsView[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return CommentsView|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
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
