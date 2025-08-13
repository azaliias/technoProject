<?php

use yii\db\Migration;

class m250813_064504_create_user_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_log}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'action' => $this->string(50)->notNull(),
            'log' => $this->json(),
            'ip_address' => $this->string(45),
            'user_agent' => $this->string(512),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx-user_log-user_id', '{{%user_log}}', 'user_id');
        $this->addForeignKey(
            'fk-user_log-user_id',
            '{{%user_log}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-user_log-user_id', '{{%user_log}}');
        $this->dropTable('{{%user_log}}');
    }
}