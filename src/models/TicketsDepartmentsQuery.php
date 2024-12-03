<?php

namespace hesabro\ticket\models;

use Yii;

/**
 * This is the ActiveQuery class for [[TicketsDepartments]].
 *
 * @see TicketsDepartments
 */
class TicketsDepartmentsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return TicketsDepartments[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return TicketsDepartments|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function active()
    {
        return $this->andOnCondition(['<>',TicketsDepartments::tableName().'.status', TicketsDepartments::STATUS_DELETED]);
    }

    public function bySlave($slave_id = null): void
    {
        $this->andOnCondition([TicketsDepartments::tableName() . '.slave_id' => $slave_id ?: Yii::$app->client->id]);
    }

	public function byCreatorId($id)
	{
		return $this->andWhere([TicketsDepartments::tableName().'.created_by' => $id]);
	}

	public function byUpdatedId($id)
	{
		return $this->andWhere([TicketsDepartments::tableName().'.updated_by' => $id]);
	}

	public function byStatus($status)
	{
		return $this->andWhere([TicketsDepartments::tableName().'.status' => $status]);
	}

	public function byId($id)
	{
		return $this->andWhere([TicketsDepartments::tableName().'.id' => $id]);
	}
}
