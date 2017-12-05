<?php
/**
 * Файл класса MessageJob
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\queuemailer\jobs;

use chulakov\queuemailer\Mailer;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\queue\Job;

class MessageJob extends BaseObject implements Job
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
     * @param \yii\queue\Queue $queue
     * @throws \yii\base\InvalidConfigException
     */
    public function execute($queue)
    {
        /** @var Mailer $mailer */
        if (!$mailer = \Yii::$app->get($this->componentName)) {
            throw new InvalidConfigException("Не существует компонента с именем componentName: {$this->componentName}.");
        }
        if ($sender = \Yii::$app->get($mailer->mailerComponent)) {
            throw new InvalidConfigException("Не существует компонента с именем mailerComponent: {$mailer->mailerComponent}.");
        }
        if (!$message = $mailer->findMessage($this->messageId)) {
            throw new InvalidConfigException("Не найдено сообщение с ID {$this->messageId}.");
        }
        // Отправка сообщения через сложенный компонент
        $message->getSwiftMessage()->send($sender);
    }
}
