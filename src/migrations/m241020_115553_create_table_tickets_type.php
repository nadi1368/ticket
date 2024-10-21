<?php

use hesabro\ticket\TicketModule;
use yii\db\Migration;

class m241020_115553_create_table_tickets_type extends Migration
{
    private $module;

    public function init(): void
    {
        $this->module = Yii::$app->getModule('tickets');
        $this->db = $this->module->db;
        parent::init();
    }

    public function safeUp()
    {
        $hasSlaves = $this->module->hasSlaves;
        $this->createTable(
            '{{%tickets_type}}',
            [
                'id' => $this->primaryKey(),
                'creator_id' => $this->integer()->unsigned(),
                'update_id' => $this->integer()->unsigned(),
                'title' => $this->string(64)->notNull(),
                'key' => $this->string(64),
                'status' => $this->integer()->unsigned(),
                'is_auto' => $this->integer()->defaultValue('0'),
                'additional_data' => $this->json(),
                'created' => $this->integer()->unsigned(),
                'changed' => $this->integer()->unsigned(),
            ]
        );
        if ($hasSlaves){
            $this->addColumn('{{%tickets_type}}', 'slave_id', $this->integer()->unsigned()->notNull());
            $this->createIndex('slave_id_index', '{{%tickets_type}}', ['slave_id']);
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%tickets_type}}');
    }
}
