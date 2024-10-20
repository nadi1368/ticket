<?php

use hesabro\ticket\TicketModule;
use yii\db\Migration;

class m241020_100925_create_table_tickets_view extends Migration
{
    public function init(): void
    {
        $this->db = TicketModule::getInstance()->db;
        parent::init();
    }

    public function safeUp()
    {
        $hasSlaves = TicketModule::getInstance()->hasSlaves;

        $this->createTable(
            '{{%tickets_view}}',
            [
                'user_id' => $this->integer()->unsigned()->notNull(),
                'comment_id' => $this->integer()->notNull(),
                'viewed' => $this->integer()->notNull()->defaultValue('0')->comment('مشاهده شده'),
                'insert_date' => $this->integer(),
            ]
        );

        if ($hasSlaves){
            $this->addColumn('{{%tickets_view}}', 'slave_id', $this->integer()->unsigned()->notNull());
            $this->createIndex('slave_id_index', '{{%tickets_view}}', ['slave_id']);
        }


        $this->addPrimaryKey('PRIMARYKEY', '{{%tickets_view}}', ['user_id', 'comment_id']);

        $this->createIndex('comment_id', '{{%tickets_view}}', ['comment_id']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%tickets_view}}');
    }
}
