<?php
/**
 * Файл класса MessageJob
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\queuemailer\jobs;

use yii\queue\Queue;
use yii\queue\JobInterface;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use chulakov\queuemailer\Mailer;
use chulakov\queuemailer\Message;
use chulakov\queuemailer\exceptions\NotFoundModelException;

/**
 * Задание с отложенной отправкой почтового сообщения
 *
 * @package chulakov\queuemailer\jobs
 */
class MessageJob extends BaseObject implements JobInterface, MessageJobInterface
{
    /**
     * @var integer Идентификатор отложенного сообщения
     */
    public $messageId;
    /**
     * @var string Идентификатор компонента отложенной рассылки почты
     */
    public $componentName;

    /**
     * Постановка в очередь отложенного сообщения
     *
     * @param Message $message
     * @param Mailer $mailer
     * @return MessageJobInterface
     */
    public static function create($message, $mailer)
    {
        return new static([
            'messageId' => $message->getMessageId(),
            'componentName' => $mailer->componentName,
        ]);
    }

    /**
     * Выполнение отложенной отправки сообщения
     *
     * @param Queue $queue
     * @throws InvalidConfigException
     * @throws NotFoundModelException
     */
    public function execute($queue)
    {
        /** @var Mailer $mailer */
        if (!$mailer = \Yii::$app->get($this->componentName, false)) {
            throw new InvalidConfigException("Не существует компонента с именем componentName: {$this->componentName}.");
        }
        if (!$sender = \Yii::$app->get($mailer->mailerComponent, false)) {
            throw new InvalidConfigException("Не существует компонента с именем mailerComponent: {$mailer->mailerComponent}.");
        }
        if (!$message = $mailer->findMessage($this->messageId, false)) {
            throw new InvalidConfigException("Не найдено сообщение с ID {$this->messageId}.");
        }
        // Отправка сообщения через сложенный компонент
        if ($message->getSwiftMessage()->send($sender)) {
            $message->clearAttachments();
        }
    }
}
