<?php

use yii\db\Migration;

class m241128_115555_add_department_id_to_tickets_table extends Migration
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
        $this->addColumn('{{%tickets}}', 'department_id', $this->integer()->unsigned()->null());
        $this->createIndex('department_id', '{{%tickets}}', ['department_id']);
    }

    public function safeDown()
    {
        $this->dropIndex('department_id', '{{%tickets}}');
        $this->dropColumn('{{%tickets}}', 'department_id');
    }
}
