<?php

use yii\db\Schema;
use yii\db\Migration;

class m150402_190400_admin_export_change_field_size extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%admin_export_requests}}', 'data_raw', Schema::TYPE_TEXT);
    }

    public function down()
    {
        $this->alterColumn('{{%admin_export_requests}}', 'data_raw', Schema::TYPE_STRING);
    }
}
