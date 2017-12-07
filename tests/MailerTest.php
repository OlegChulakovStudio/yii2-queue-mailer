<?php
/**
 * Файл класса MailerTest
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\queuemailer\tests;

use chulakov\queuemailer\Mailer;
use chulakov\queuemailer\Message;
use chulakov\queuemailer\tests\models\QueueTestMail;

class MailerTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateMessage()
    {
        $mailer = new Mailer([
            'storageClass' => QueueTestMail::class,
        ]);
        $this->assertInstanceOf(Message::class, $mailer->createMessage());
    }

    public function testFindMessage()
    {
        $mailer = new Mailer([
            'storageClass' => QueueTestMail::class,
        ]);
        $this->assertInstanceOf(Message::class, $mailer->findMessage(1));
    }

    public function testSettingMessage()
    {
        $from = 'admin@mail.ru';
        $subject = 'Test setting message';
        $replyTo = ['reply@mail.ru' => 'reply'];
        $mailer = new Mailer([
            'storageClass' => QueueTestMail::class,
            'messageConfig' => [
                'from' => $from,
                'replyTo' => $replyTo,
                'subject' => $subject,
            ],
        ]);
        $this->assertEquals([$from => ''], $mailer->compose()->getFrom());
        $this->assertEquals($subject, $mailer->compose()->getSubject());
        $this->assertEquals($replyTo, $mailer->compose()->getReplyTo());
    }
}
