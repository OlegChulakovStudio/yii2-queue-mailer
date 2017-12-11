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
use chulakov\queuemailer\serializers\JsonSerializer;
use chulakov\queuemailer\tests\models\QueueTestMail;
use chulakov\queuemailer\exceptions\NotFoundModelException;

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

    public function testNotFoundMessage()
    {
        $mailer = new Mailer([
            'storageClass' => QueueTestMail::class,
        ]);
        $this->assertEquals(null, $mailer->findMessage(0, false));
    }

    public function testNotFoundMessageException()
    {
        $mailer = new Mailer([
            'storageClass' => QueueTestMail::class,
        ]);
        $this->expectException(NotFoundModelException::class);
        $massage = $mailer->findMessage(0);
    }

    public function testSettingMessage()
    {
        $from = 'admin@mail.ru';
        $subject = 'Test setting message';
        $replyTo = ['reply@mail.ru' => 'reply'];
        $mailer = new Mailer([
            'storageClass' => QueueTestMail::class,
            'messageConfig' => [
                'serializer' => JsonSerializer::class,
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
