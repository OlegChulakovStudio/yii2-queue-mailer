<?php
/**
 * Файл класса MessageTest
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\queuemailer\tests;

use chulakov\queuemailer\Message;
use chulakov\queuemailer\models\QueueMail;
use yii\base\InvalidConfigException;

class MessageTest extends \PHPUnit\Framework\TestCase
{
    /** @var Message */
    protected static $message;

    protected function setUp()
    {
        parent::setUp();
        $post = $this->getMockBuilder(QueueMail::class)
            ->setMethods(['save', 'attributes'])
            ->getMock();
        $post->method('save')->willReturn(true);
        $post->method('attributes')->willReturn([
            'id', 'to', 'from', 'subject', 'priority', 'text', 'html',
            'charset', 'reply_to', 'cc', 'bcc', 'return_path', 'read_receipt_to',
            'attachments', 'embeds', 'signs', 'headers', 'created_at', 'created_by',
        ]);
        static::$message = new Message($post, []);
    }

    protected function tearDown()
    {
        parent::tearDown();
        static::$message = null;
    }

    public function testSetEmails()
    {
        $message = static::$message;

        $message->setTo($to = 'to@mail.ru');
        $message->setFrom($from = 'from@mail.ru');
        $message->setCC([
            $cco = 'cco@mail.ru', $cct = 'cct@mail.ru'
        ]);
        $message->setBcc($bcc = ['bcc@mail.ru' => 'bcc']);
        $message->setReplyTo($replyTo = ['reply@mail.ru' => 'reply']);
        $message->setReturnPath($return = 'return@mail.ru');
        $message->setReadReceiptTo($read = 'read@mail.ru');

        $this->assertEquals([$to => ''], $message->getTo());
        $this->assertEquals([$from => ''], $message->getFrom());
        $this->assertEquals([$cco => '', $cct => ''], $message->getCc());
        $this->assertEquals($bcc, $message->getBcc());
        $this->assertEquals($replyTo, $message->getReplyTo());
        $this->assertEquals($return, $message->getReturnPath());
        $this->assertEquals($read, $message->getReadReceiptTo());
    }

    public function testSetBody()
    {
        $message = static::$message;

        $message->setCharset($charset = 'utf8');
        $message->setSubject($subject = 'Test subject');
        $message->setPriority($priority = 5);
        $message->setTextBody($text = 'Test text message body.');
        $message->setHtmlBody($html = '<h1>Test html message body</h1>');

        $this->assertEquals($charset, $message->getCharset());
        $this->assertEquals($subject, $message->getSubject());
        $this->assertEquals($priority, $message->getPriority());
        $this->assertEquals($text, $message->getTextBody());
        $this->assertEquals($html, $message->getHtmlBody());
    }

    public function testSetHeader()
    {
        $message = static::$message;

        $message->setHeader($name = 'X-Path', $value = 'path');

        $this->assertEquals([$value], $message->getHeader($name));
    }

    public function testSetHeaderArray()
    {
        $message = static::$message;

        $message->setHeader($name = 'X-Path', $value = ['path', 'to', 'header']);

        $this->assertEquals($value, $message->getHeader($name));
    }

    public function testAddHeader()
    {
        $message = static::$message;

        $message->setHeader($name = 'X-Path', $value = 'path');
        $message->addHeader($name, $add = 'add');

        $this->assertEquals([$value, $add], $message->getHeader($name));
    }

    public function testSetHeadersList()
    {
        $message = static::$message;

        $message->setHeaders([
            $fName = 'X-First' => $fValue = 'value',
            $sName = 'X-Second' => $sValue = ['second', 'value'],
        ]);

        $this->assertEquals([$fValue], $message->getHeader($fName));
        $this->assertEquals($sValue, $message->getHeader($sName));
    }

    public function testSetSignatureArray()
    {
        $message = static::$message;

        $message->setSignature($sign = [
            'type' => 'dkim',
            'key' => 'private key',
        ]);

        $signs = $this->getProtectedField($message, 'signs');

        $this->assertEquals([$sign], $signs);
    }

    public function testSetSignatureCallback()
    {
        $message = static::$message;

        $callback = function () {
            return [
                'type' => 'dkim',
                'key' => 'private key',
            ];
        };

        $message->setSignature($callback);

        $signs = $this->getProtectedField($message, 'signs');

        $this->assertEquals([call_user_func($callback)], $signs);
    }

    public function testSetInvalidSignature()
    {
        $message = static::$message;

        $this->expectException(InvalidConfigException::class);

        $message->setSignature($sign = [
            'key' => 'private key',
        ]);
    }

    public function testAddSignature()
    {
        $message = static::$message;

        $message->setSignature($one = [
            'type' => 'dkim',
            'key' => 'private key',
        ]);
        $message->addSignature($two = [
            'type' => 'opendkim',
            'key' => 'private key',
        ]);

        $signs = $this->getProtectedField($message, 'signs');

        $this->assertEquals([$one, $two], $signs);
    }

    public function testGetSwiftMessage()
    {
        $message = static::$message;

        $message->setTo($to = 'to@mail.ru');
        $message->setFrom($from = 'from@mail.ru');
        $message->setReplyTo($replyTo = 'reply@mail.ru');
        $message->setCharset($charset = 'utf8');
        $message->setPriority(2);
        $message->setSubject($subject = 'Test subject');
        $message->setTextBody($text = 'Test text message body.');
        $message->setHtmlBody($html = '<h1>Test html message body</h1>');

        $swift = $message->getSwiftMessage();

        $this->assertEquals([$to => ''], $swift->getTo());
        $this->assertEquals([$from => ''], $swift->getFrom());
        $this->assertEquals([$replyTo => ''], $swift->getReplyTo());
        $this->assertEquals($subject, $swift->getSubject());

        $string = $swift->toString();

        $this->assertContains('charset=' . $charset, $string);
        $this->assertContains('From: ' . $from, $string);
        $this->assertContains('Reply-To: ' . $replyTo, $string);
        $this->assertContains('To: ' . $to, $string);
        $this->assertContains("X-Priority: 2 (High)", $string);
        $this->assertContains('Subject: ' . $subject, $string);
        $this->assertContains($text, $string);
        $this->assertContains($html, $string);
    }

    protected function getProtectedField(Message $message, $name = '')
    {
        $reflection = new \ReflectionObject($message);
        $fieldReflection = $reflection->getProperty($name);
        $fieldReflection->setAccessible(true);
        return $fieldReflection->getValue($message);
    }
}
