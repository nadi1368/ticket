<?php

use yii\db\Migration;

class m241127_115553_create_table_tickets_departments extends Migration
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
            '{{%tickets_departments}}',
            [
                'id' => $this->primaryKey(),
                'title' => $this->string(64)->notNull(),
                'status' => $this->integer()->unsigned(),
                'created_at' => $this->integer()->unsigned()->notNull(),
                'created_by' => $this->integer()->unsigned(),
                'updated_at' => $this->integer()->unsigned()->notNull(),
                'updated_by' => $this->integer()->unsigned(),
            ]
        );
        if ($hasSlaves) {
            $this->addColumn('{{%tickets_departments}}', 'slave_id', $this->integer()->unsigned()->notNull());
            $this->createIndex('slave_id_index', '{{%tickets_departments}}', ['slave_id']);
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%tickets_departments}}');
    }
}
