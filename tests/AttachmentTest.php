<?php
/**
 * Файл класса AttachmentTest
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\queuemailer\tests;

use chulakov\queuemailer\AttacheStorage;
use chulakov\queuemailer\Message;
use chulakov\queuemailer\tests\models\QueueTestMail;
use PHPUnit\Framework\TestCase;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

class AttachmentTest extends TestCase
{
    protected static $data;
    protected static $alias;
    protected static $path;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::$alias = '@chulakov/queuemailer/tests/runtime/' . uniqid('', true);
        static::$data = \Yii::getAlias('@chulakov/queuemailer/tests/data');
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

    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication([]);
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->destroyApplication();
    }

    public function testAttacheFile()
    {
        /** @var Message $message */
        $message = \Yii::$app->get('mailer')->compose();
        $message->attach($fileName = static::$data . '/test.png');

        $attach = $this->getAttachment($message);

        $this->assertTrue(is_object($attach));
        $this->assertInstanceOf(\Swift_Attachment::class, $attach);
        $this->assertContains($attach->getFilename(), $fileName);
    }

    public function testAttacheContent()
    {
        /** @var Message $message */
        $message = \Yii::$app->get('mailer')->compose();
        $message->attachContent('Test log text', [
            'fileName' => $fileName = 'test.log',
            'contentType' => $contentType = 'text/plain',
        ]);

        $attach = $this->getAttachment($message);

        $this->assertTrue(is_object($attach));
        $this->assertInstanceOf(\Swift_Attachment::class, $attach);
        $this->assertEquals($fileName, $attach->getFilename());
        $this->assertEquals($contentType, $attach->getContentType());
    }

    public function testEmbedFile()
    {
        /** @var Message $message */
        $message = \Yii::$app->get('mailer')->compose();
        $cid = $message->embed($fileName = static::$data . '/test.png');

        /** @var \Swift_EmbeddedFile $attach */
        $attach = $this->getAttachment($message);

        $this->assertTrue(is_object($attach));
        $this->assertInstanceOf(\Swift_EmbeddedFile::class, $attach);
        $this->assertContains($attach->getFilename(), $fileName);
        $this->assertEquals($cid, 'cid:' . $attach->getId());
    }

    public function testEmbedContent()
    {
        /** @var Message $message */
        $message = \Yii::$app->get('mailer')->compose();
        $cid = $message->embedContent('Test log text.', [
            'fileName' => $fileName = 'test.log',
            'contentType' => $contentType = 'text/plain',
        ]);

        /** @var \Swift_EmbeddedFile $attach */
        $attach = $this->getAttachment($message);

        $this->assertTrue(is_object($attach));
        $this->assertInstanceOf(\Swift_EmbeddedFile::class, $attach);
        $this->assertEquals($fileName, $attach->getFilename());
        $this->assertEquals($contentType, $attach->getContentType());
        $this->assertEquals($cid, 'cid:' . $attach->getId());
    }

    protected function getAttachment(Message $message)
    {
        $messageParts = $message->getSwiftMessage()
            ->getSwiftMessage()
            ->getChildren();

        $attachment = null;
        foreach ($messageParts as $part) {
            if ($part instanceof \Swift_Mime_Attachment) {
                $attachment = $part;
                break;
            }
        }
        return $attachment;
    }

    protected function mockApplication($config = [], $appClass = '\yii\console\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
            'components' => [
                'mailer' => new \chulakov\queuemailer\Mailer([
                    'storageClass' => QueueTestMail::class,
                    'mailerComponent' => 'swiftmailer',
                    'componentName' => 'mailer',
                ]),
                'attachment' => new AttacheStorage([
                    'storageAll' => true,
                    'storagePath' => static::$alias,
                ]),
                'swiftmailer' => new \yii\swiftmailer\Mailer([
                    'useFileTransport' => true,
                    'fileTransportPath' => static::$alias,
                ]),
            ]
        ], $config));
    }

    protected function destroyApplication()
    {
        \Yii::$app = null;
    }
}
