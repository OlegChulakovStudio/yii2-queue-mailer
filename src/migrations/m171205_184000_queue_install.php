<?php
/**
 * Файл класса m171205_184000_queue_install
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

use yii\db\Migration;

class m171205_184000_queue_install extends Migration
{
    public function up()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%queue_mail}}', [
            'id' => $this->primaryKey(),

            'to' => $this->text()->notNull(),
            'from' => $this->text()->notNull(),
            'subject' => $this->text()->notNull(),
            'priority' => $this->smallInteger(1),
            'text' => $this->text(),
            'html' => $this->text(),
            'reply_to' => $this->text(),
            'cc' => $this->text(),
            'bcc' => $this->text(),
            'charset' => $this->string(),
            'return_path' => $this->string(),
            'read_receipt_to' => $this->string(),
            'attachments' => $this->text(),
            'embeds' => $this->text(),
            'signs' => $this->text(),
            'headers' => $this->text(),

            'created_at' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%queue_mail}}');
    }
}