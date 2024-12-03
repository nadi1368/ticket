<?php

namespace hesabro\ticket\models;

use common\components\Client;
use hesabro\ticket\TicketModule;
use Yii;

/**
 * This is the ActiveQuery class for [[Comments]].
 *
 * @see Comments
 */
class TicketsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Tickets[]|array
     */
    public function all($db = null): array
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Tickets|array|null
     */
    public function one($db = null): Tickets|array|null
    {
        return parent::one($db);
    }

    public function my(): self
    {
        return $this->joinWith(['commentsViews', 'department.usersPivot'])->andWhere([
            'OR',
            [TicketsView::tableName() . '.user_id' => Yii::$app->user->id],
            [TicketsDepartmentUsers::tableName() . '.user_id' => Yii::$app->user->id],
            [Tickets::tableName() . '.creator_id' => Yii::$app->user->id]
        ]);
    }

    public function inbox(): self
    {
        return $this->joinWith(['commentsViews', 'department.usersPivot'])->andWhere([
            'OR',
            [TicketsView::tableName() . '.user_id' => Yii::$app->user->id],
            [TicketsDepartmentUsers::tableName() . '.user_id' => Yii::$app->user->id],
            TicketModule::getInstance()->hasSlaves && Yii::$app->client->isMaster() ? [Tickets::tableName() . '.type' => Tickets::TYPE_MASTER] : [],
        ]);
    }

    public function excludeViewedThreads(): self
    {
        $this->andWhere([
            'or',
            ['parent_id' => 0],
            [
                'and',
                ['<>', 'parent_id', 0],
                ['<>', 'kind', Tickets::KIND_THREAD],
            ],
            [
                'and',
                ['<>', 'parent_id', 0],
                ['kind' => Tickets::KIND_THREAD],
                ['viewed' => 0]
            ]
        ]);

        return $this;
    }

    public function unread(): self
    {
        return $this->inbox()->andWhere([TicketsView::tableName() . '.viewed' => 0]);
    }

    public function doing(): self
    {
        return $this->joinWith(['commentsViews'])->andWhere(['user_id' => Yii::$app->user->id])->andWhere(['status' => Tickets::STATUS_DOING]);
    }

    public function closed(): self
    {
        return $this->joinWith(['commentsViews'])->andWhere(['user_id' => Yii::$app->user->id])->andWhere(['status' => Tickets::STATUS_CLOSE]);
    }

    public function outbox(): self
    {
        return $this->andWhere([
            'AND',
            ['creator_id' => Yii::$app->user->id],
            ['type' => [Tickets::TYPE_PRIVATE, Tickets::TYPE_DEPARTMENT, Tickets::TYPE_MASTER]]
        ])
            ->andWhere(['parent_id' => 0]);
    }

    public function active(): self
    {
        return $this->andOnCondition(['<>', Tickets::tableName() . '.status', Tickets::STATUS_DELETED]);
    }

    public function bySlave(): void
    {
        $this->andOnCondition([Tickets::tableName() . '.slave_id' => Yii::$app->client->id]);
    }

    public function byClass($class_name, $class_id): self
    {
        return $this
            ->andWhere(['class_name' => $class_name, 'class_id' => $class_id])
            ->andWhere('class_name IS NOT NULL')
            ->andWhere('class_id IS NOT NULL');
    }

    public function byParent($parent_id): self
    {
        return $this->andWhere(['parent_id' => $parent_id]);
    }

    public function byClassname($class_name): self
    {
        return $this->andWhere(['class_name' => $class_name]);
    }

    public function byClassIds(array $ids): self
    {
        return $this->andWhere(['in', 'class_id', $ids]);
    }

    /**
     * Query by owner ids
     */
    public function byOwnerIds(array $ids): self
    {
        return $this->joinWith(['commentsViews'])->andWhere(['in', TicketsView::tableName() . '.user_id', $ids]);
    }

    public function parentOrRefer(): self
    {
        $this->andWhere([
            'or',
            [
                Tickets::tableName() . '.parent_id' => 0
            ],
            [
                Tickets::tableName() . '.kind' => Tickets::KIND_REFER
            ]
        ]);

        return $this;
    }

    public function isParent(): self
    {
        $this->andWhere([
            Tickets::tableName() . '.parent_id' => 0
        ]);

        return $this;
    }

    public function isSystem(): self
    {
        $this->andWhere([
            Tickets::tableName() . '.creator_id' => 0
        ]);

        return $this;
    }

    public function isNotSystem(): self
    {
        $this->andWhere(['<>', Tickets::tableName() . '.creator_id', '0']);

        return $this;
    }
}
