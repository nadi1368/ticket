<?php

use yii\db\Migration;

/**
 * Class m241212_084602_change_tickets_view_primary_key
 */
class m241212_084602_change_tickets_view_primary_key extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropPrimaryKey('PRIMARY', '{{%tickets_view}}');
        $this->addPrimaryKey('PRIMARY', '{{%tickets_view}}', ['user_id', 'comment_id', 'slave_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropPrimaryKey('PRIMARY', '{{%tickets_view}}');
        $this->addPrimaryKey('PRIMARY', '{{%tickets_view}}', ['user_id', 'comment_id']);
    }
}
