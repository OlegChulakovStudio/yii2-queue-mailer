<?php
/**
 * Файл класса m180410_183000_queue_required
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

use yii\db\Migration;

class m180410_183000_queue_required extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%queue_mail}}', 'to', $this->text());
        $this->alterColumn('{{%queue_mail}}', 'from', $this->text());
        $this->alterColumn('{{%queue_mail}}', 'subject', $this->string());
    }

    public function down()
    {
        $this->alterColumn('{{%queue_mail}}', 'to', $this->text()->notNull());
        $this->alterColumn('{{%queue_mail}}', 'from', $this->text()->notNull());
        $this->alterColumn('{{%queue_mail}}', 'subject', $this->text()->notNull());
    }
}
