<?php

use yii\db\Migration;

class m241127_115554_create_table_tickets_department_users extends Migration
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
            '{{%tickets_department_users}}',
            [
                'id' => $this->primaryKey(),
                'department_id' => $this->integer(),
                'user_id' => $this->integer()->unsigned(),
                'created_at' => $this->integer()->unsigned()->notNull(),
                'created_by' => $this->integer()->unsigned(),
                'updated_at' => $this->integer()->unsigned()->notNull(),
                'updated_by' => $this->integer()->unsigned(),
            ]
        );
        $this->createIndex('department_id', '{{%tickets_department_users}}', ['department_id']);
        $this->createIndex('user_id', '{{%tickets_department_users}}', ['user_id']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%tickets_department_users}}');
    }
}
