<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%loan_requests}}`.
 */
class m241104_000001_create_loan_requests_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%loan_requests}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'amount' => $this->integer()->notNull(),
            'term' => $this->integer()->notNull(),
            'status' => $this->string(20)->defaultValue('pending'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex(
            'idx-loan_requests-user_id',
            '{{%loan_requests}}',
            'user_id'
        );

        $this->createIndex(
            'idx-loan_requests-status',
            '{{%loan_requests}}',
            'status'
        );

        $this->execute("ALTER TABLE {{%loan_requests}} ADD CONSTRAINT chk_status CHECK (status IN ('pending', 'approved', 'declined'))");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%loan_requests}}');
    }
}