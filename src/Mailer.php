<?php
/**
 * Файл класса Mailer
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\queuemailer;

use chulakov\queuemailer\jobs\MessageJob;
use chulakov\queuemailer\models\MailStorageInterface;
use yii\mail\BaseMailer;
use yii\mail\MessageInterface;

class Mailer extends BaseMailer
{
    /**
     * @var string Класс объекта сообщения
     */
    public $messageClass = 'chulakov\queuemailer\Message';
    /**
     * @var string Класс модели хранения данных из письма
     */
    public $storageClass = 'chulakov\queuemailer\models\QueueMail';
    /**
     * @var string Имя компонента для обработки прикрепляемых файлов
     */
    public $attacheComponent = 'attachment';
    /**
     * @var string Имя компонента для моментальной отправки почты
     */
    public $mailerComponent = 'mailer';
    /**
     * @var string Имя компонент отложенной отправки сообщения, которое будет использоваться из задания в очереди
     */
    public $componentName = 'queuemailer';
    /**
     * @var string Имя компонента постановки задания в очередь
     */
    public $queueComponent = 'queue';

    /**
     * Сохраняет сообщение в очереди для отправки.
     * Если сообщение не удалось сохранить, будет осуществлена попытка отправить письмо сразу.
     *
     * @param MessageInterface|Message $message
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    protected function sendMessage($message)
    {
        // Попытка поставить в очередь
        if (!$this->saveMessage($message)) {
            // Попытка отправить почту напрямую
            if ($mailer = \Yii::$app->get($this->mailerComponent, false)) {
                return $mailer->send($message->getSwiftMessage());
            }
        }
        return false;
    }

    /**
     * Сохраняет сообщение в очереди для отправки
     *
     * @param MessageInterface|Message $message
     * @return bool
     */
    protected function saveMessage($message)
    {
        // Попытка поставить в очередь
        if ($queue = \Yii::$app->get($this->queueComponent, false)) {
            return $queue->push(new MessageJob([
                'messageId' => $message->id,
                'componentName' => $this->componentName,
            ]));
        }
        return false;
    }

    /**
     * Создание нового сообщения
     *
     * @return Message|object
     * @throws \yii\base\InvalidConfigException
     */
    public function createMessage()
    {
        return $this->buildMessage(new $this->storageClass, $this->messageConfig);
    }

    /**
     * Поиск сообщения по его ID в очереди
     *
     * @param integer $id
     * @return Message|object
     * @throws \yii\base\InvalidConfigException
     */
    public function findMessage($id)
    {
        /** @var MailStorageInterface $class */
        $class = $this->storageClass;
        if (!$mail = $class::findById($id)) {
            $mail = new $class();
        }
        return $this->buildMessage($mail, $this->messageConfig);
    }

    /**
     * Построитель объкта сообщения
     *
     * @param MailStorageInterface $mail
     * @param array $config
     * @return Message|object
     * @throws \yii\base\InvalidConfigException
     */
    protected function buildMessage($mail, $config)
    {
        if (!array_key_exists('class', $config)) {
            $config['class'] = $this->messageClass;
        }
        $config['mailer'] = $this;
        $config['attacheComponent'] = $this->attacheComponent;
        return \Yii::createObject($config, [$mail]);
    }
}
