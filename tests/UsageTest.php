<?php
/**
 * Файл класса UsageTest
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace yiiunit\extensions\swiftmailer;

use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use PHPUnit\Framework\TestCase;
use chulakov\queuemailer\Message;
use chulakov\queuemailer\models\QueueMail;

class UsageTest extends TestCase
{
    protected static $db;
    protected static $alias;
    protected static $path;
    protected static $mail = 'test.eml';

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::$db = 'sqlite:@chulakov/queuemailer/tests/data/tests.db';
        static::$alias = '@chulakov/queuemailer/tests/runtime/' . uniqid('us.', true);
        static::$path  = \Yii::getAlias(static::$alias);
        if (!file_exists(static::$path)) {
            FileHelper::createDirectory(static::$path);
        }
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        if (file_exists(static::$path)) {
            FileHelper::removeDirectory(static::$path);
        }
    }

    protected function tearDown()
    {
        parent::tearDown();
        \Yii::$app->db->createCommand()
            ->truncateTable(QueueMail::tableName())
            ->execute();
        $this->destroyApplication();
    }

    public function testSendMailerMessage()
    {
        $this->mockApplication([]);
        $message = \Yii::$app->mailer->compose()
            ->setTo('to@mail.ru')
            ->setFrom('from@mail.ru')
            ->setSubject($subject = 'Test subject')
            ->setTextBody($body = 'Test text mail body.');

        $this->assertTrue($message->send());
        $this->assertSendingMail($subject, $body);
    }

    public function testSendQueueMessage()
    {
        $this->mockApplication(['components' => [
            'queue' => [
                'class' => 'yii\queue\file\Queue',
                'path' => static::$alias,
            ]
        ]]);
        /** @var Message $message */
        $message = \Yii::$app->mailer->compose()
            ->setTo('to@mail.ru')
            ->setFrom('from@mail.ru')
            ->setSubject($subject = 'Test subject')
            ->setTextBody($body = 'Test text mail body.');
        $this->assertTrue($message->send());

        \Yii::$app->get('queue')->run(0);

        $this->assertSendingMail($subject, $body);
    }

    protected function assertSendingMail($subject, $body)
    {
        $mailPath = static::$path . '/' . static::$mail;
        $mailContent = file_get_contents($mailPath);

        $this->assertTrue(is_file($mailPath));
        $this->assertContains($subject, $mailContent);
        $this->assertContains($body, $mailContent);
    }

    protected function mockApplication($config = [], $appClass = '\yii\console\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
            'components' => [
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => static::$db,
                ],
                'mutex' => [
                    'class' => 'yii\mutex\FileMutex',
                    'mutexPath' => static::$alias,
                ],
                'mailer' => [
                    'class' => 'chulakov\queuemailer\Mailer',
                    'mailerComponent' => 'swiftmailer',
                    'componentName' => 'mailer',
                ],
                'attachment' => [
                    'class' => 'chulakov\queuemailer\AttacheStorage',
                    'storagePath' => static::$alias,
                ],
                'swiftmailer' => [
                    'class' => 'yii\swiftmailer\Mailer',
                    'useFileTransport' => true,
                    'fileTransportPath' => static::$alias,
                    'fileTransportCallback' => function () {
                        return static::$mail;
                    }
                ],
            ]
        ], $config));
    }

    protected function destroyApplication()
    {
        \Yii::$app = null;
    }
}
