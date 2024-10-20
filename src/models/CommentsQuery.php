<?php

namespace hesabro\ticket\models;

use Yii;

/**
 * This is the ActiveQuery class for [[Comments]].
 *
 * @see Comments
 */
class CommentsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Comments[]|array
     */
    public function all($db = null): array
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Comments|array|null
     */
    public function one($db = null): Comments|array|null
    {
        return parent::one($db);
    }

    public function my(): CommentsQuery
    {
        return $this->joinWith(['commentsViews'])->andWhere(['OR', ['user_id' => Yii::$app->user->id], ['creator_id' => Yii::$app->user->id]]);
    }

    public function inbox(): CommentsQuery
    {
        return $this->joinWith(['commentsViews'])->andWhere(['user_id' => Yii::$app->user->id]);
    }

    public function excludeViewedThreads(): CommentsQuery
    {
        $this->andWhere([
            'or',
            ['parent_id' => 0],
            [
                'and',
                ['<>', 'parent_id', 0],
                ['<>', 'kind', Comments::KIND_THREAD],
            ],
            [
                'and',
                ['<>', 'parent_id', 0],
                ['kind' => Comments::KIND_THREAD],
                ['viewed' => 0]
            ]
        ]);

        return $this;
    }

    public function unread(): CommentsQuery
    {
        return $this->inbox()->andWhere(['viewed' => 0]);
    }

    public function inboxMaster(): CommentsQuery
    {
        return $this->andWhere(['type' => Comments::TYPE_MASTER])->andWhere(['status' => Comments::STATUS_ACTIVE]);
    }

    public function unreadMaster(): CommentsQuery
    {
        return $this->inboxMaster()->andWhere(['viewed' => 0]);
    }

    public function doing(): CommentsQuery
    {
        return $this->joinWith(['commentsViews'])->andWhere(['user_id' => Yii::$app->user->id])->andWhere(['status' => Comments::STATUS_DOING]);
    }

    public function doingMaster(): CommentsQuery
    {
        return $this->andWhere(['type' => Comments::TYPE_MASTER])->andWhere(['status' => Comments::STATUS_DOING]);
    }

    public function closed(): CommentsQuery
    {
        return $this->joinWith(['commentsViews'])->andWhere(['user_id' => Yii::$app->user->id])->andWhere(['status' => Comments::STATUS_CLOSE]);
    }

    public function closedMaster(): CommentsQuery
    {
        return $this->andWhere(['type' => Comments::TYPE_MASTER])->andWhere(['status' => Comments::STATUS_CLOSE]);
    }

    public function outbox(): CommentsQuery
    {
        return $this->andWhere(['AND', ['creator_id' => Yii::$app->user->id], ['type' => [Comments::TYPE_PRIVATE, Comments::TYPE_MASTER]]])->andWhere(['parent_id' => 0]);
    }

    public function active(): CommentsQuery
    {
        return $this->andWhere(['<>', Comments::tableName() . '.status', Comments::STATUS_DELETED]);
    }

    public function byClass($class_name, $class_id): CommentsQuery
    {
        return $this
            ->andWhere(['class_name' => $class_name, 'class_id' => $class_id])
            ->andWhere('class_name IS NOT NULL')
            ->andWhere('class_id IS NOT NULL');
    }

    public function byParent($parent_id): CommentsQuery
    {
        return $this->andWhere(['parent_id' => $parent_id]);
    }

    public function byClassname($class_name): CommentsQuery
    {
        return $this->andWhere(['class_name' => $class_name]);
    }

    public function byClassIds(array $ids): CommentsQuery
    {
        return $this->andWhere(['in', 'class_id', $ids]);
    }

    /**
     * Query by owner ids
     */
    public function byOwnerIds(array $ids): CommentsQuery
    {
        return $this->joinWith(['commentsViews'])->andWhere(['in', CommentsView::tableName() . '.user_id', $ids]);
    }

    public function parentOrRefer(): CommentsQuery
    {
        $this->andWhere([
            'or',
            [
                'parent_id' => 0
            ],
            [
                'kind' => Comments::KIND_REFER
            ]
        ]);

        return $this;
    }

    public function isParent(): CommentsQuery
    {
        $this->andWhere([
            'parent_id' => 0
        ]);

        return $this;
    }

    public function isSystem(): CommentsQuery
    {
        $this->andWhere([
            'creator_id' => 0
        ]);

        return $this;
    }

    public function isNotSystem(): CommentsQuery
    {
        $this->andWhere(['<>', 'creator_id', '0']);

        return $this;
    }
}
