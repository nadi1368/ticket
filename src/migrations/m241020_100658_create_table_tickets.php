<?php

use hesabro\ticket\TicketModule;
use yii\db\Migration;

class m241020_100658_create_table_tickets extends Migration
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
            '{{%tickets}}',
            [
                'id' => $this->primaryKey(),
                'parent_id' => $this->integer()->notNull()->defaultValue('0'),
                'creator_id' => $this->integer()->unsigned()->notNull(),
                'update_id' => $this->integer()->unsigned()->notNull(),
                'kind' => $this->tinyInteger()->defaultValue(1),
                'type' => $this->integer()->notNull()->defaultValue('0')->comment('عمومی یا خصوصی'),
                'type_task' => $this->integer()->defaultValue(0)->unsigned(),
                'class_name' => $this->string(64),
                'class_id' => $this->integer(),
                'link' => $this->string(64),
                'title' => $this->string(128),
                'des' => $this->text()->null(),
                'css_class' => $this->integer()->notNull(),
                'status' => $this->integer()->notNull(),
                'due_date' => $this->string(10),
                'file_name' => $this->string(256),
                'created' => $this->integer()->unsigned()->notNull(),
                'changed' => $this->integer()->unsigned()->notNull(),
                'additional_data' => $this->json()->null()
            ]
        );

        if ($hasSlaves){
            $this->addColumn('{{%tickets}}', 'slave_id', $this->integer()->unsigned()->notNull());
            $this->createIndex('slave_id_index', '{{%comments_view}}', ['slave_id']);
        }

        $this->createIndex('creator_id', '{{%comments}}', ['creator_id']);
        $this->createIndex('update_id', '{{%comments}}', ['update_id']);
        $this->createIndex('parent_id', '{{%comments}}', ['parent_id']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%tickets}}');
    }
}
