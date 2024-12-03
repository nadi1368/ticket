<?php

use yii\db\Migration;

class m241128_115556_add_priority_to_tickets_table extends Migration
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
        $this->dropColumn('{{%tickets}}', 'css_class');
        $this->addColumn('{{%tickets}}', 'priority', $this->integer()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%tickets}}', 'priority');
        $this->addColumn('{{%tickets}}', 'css_class', $this->integer()->notNull());
    }
}
