<?php
/**
 * Файл класса Mailer
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\queuemailer;

use chulakov\queuemailer\jobs\MessageJob;
use yii\mail\BaseMailer;
use yii\mail\MessageInterface;

class Mailer extends BaseMailer
{
    /**
     * @var string Класс объекта сообщения
     */
    public $messageClass = 'chulakov\queuemailer\Message';
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
     */
    protected function sendMessage($message)
    {
        // Попытка поставить в очередь
        if (!$this->saveMessage($message)) {
            // Попытка отправить почту напрямую
            if ($mailer = \Yii::$app->get($this->mailerComponent)) {
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
        if ($queue = \Yii::$app->get($this->queueComponent)) {
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
        return $this->buildMessage($this->messageConfig);
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
        return $this->buildMessage(array_merge(
            $this->messageConfig, ['id' => $id]
        ));
    }

    /**
     * Построитель объкта сообщения
     *
     * @param array $config
     * @return Message|object
     * @throws \yii\base\InvalidConfigException
     */
    protected function buildMessage($config)
    {
        if (!array_key_exists('class', $config)) {
            $config['class'] = $this->messageClass;
        }
        $config['mailer'] = $this;
        $config['attacheComponent'] = $this->attacheComponent;
        return \Yii::createObject($config);
    }
}
