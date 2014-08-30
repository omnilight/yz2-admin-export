<?php

use yii\db\Schema;
use yii\db\Migration;

class m140830_162724_admin_export_initial extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'ENGINE=InnoDB CHARSET=utf8';
        }

        $this->createTable('{{%admin_export_requests}}', [
            'id' => Schema::TYPE_PK,
            'user_id' => Schema::TYPE_INTEGER,
            'data_raw' => Schema::TYPE_STRING,
            'created_at' => Schema::TYPE_DATETIME,
            'updated_at' => Schema::TYPE_DATETIME,
            'file' => Schema::TYPE_STRING,
            'is_exported' => Schema::TYPE_BOOLEAN.' DEFAULT 0',
            'exported_at' => Schema::TYPE_DATETIME,
            'FOREIGN KEY (user_id) REFERENCES {{%admin_users}} (id) ON DELETE CASCADE ON UPDATE CASCADE',
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%admin_export_requests}}');
    }
}
