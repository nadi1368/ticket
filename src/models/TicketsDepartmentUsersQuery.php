<?php

namespace hesabro\ticket\models;

/**
 * This is the ActiveQuery class for [[TicketsDepartmentUsers]].
 *
 * @see TicketsDepartmentUsers
 */
class TicketsDepartmentUsersQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return TicketsDepartmentUsers[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return TicketsDepartmentUsers|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
