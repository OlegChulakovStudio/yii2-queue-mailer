<?php
/**
 * Файл класса Message
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\queuemailer;

use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\mail\MailerInterface;
use yii\mail\MessageInterface;
use chulakov\queuemailer\models\QueueMail;
use chulakov\queuemailer\models\Attachment;

class Message extends BaseObject implements MessageInterface
{
    /**
     * Идентификатор существующей модели письма
     *
     * @var integer
     */
    public $id;
    /**
     * Компонент работы с прикрепляемыми файлами к письму
     *
     * @var string
     */
    public $attacheComponent = 'attachment';
    /**
     * Компонент для отправки почты
     *
     * @var MailerInterface
     */
    public $mailer;

    /**
     * Модель с данными о письме
     *
     * @var QueueMail
     */
    protected $mail;
    /**
     * Массив вложений
     *
     * @var array
     */
    protected $attachments = [];
    /**
     * Массив внедрений
     *
     * @var array
     */
    protected $embeds = [];
    /**
     * Массив подписей
     *
     * @var array
     */
    protected $signs = [];
    /**
     * Массив дополнительных заголовков
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Идентификатор данного письма. Для записи всех вложений в папку с данным ID
     *
     * @var string
     */
    protected $messageId;

    /**
     * Развертывание данных из модели
     */
    public function init()
    {
        $this->messageId = hash("crc32b", md5(uniqid() . microtime(true)));
        if (!is_null($this->id)) {
            $this->mail = QueueMail::findOne($this->id);
        }
        if (empty($this->mail)) {
            $this->mail = new QueueMail();
        }
        $arrays = ['attachments', 'embeds', 'signs', 'headers'];
        foreach ($arrays as $key) {
            if (!empty($this->mail->{$key})) {
                if ($items = $this->unserialize($this->mail->{$key})) {
                    $this->{$key} = $items;
                }
            }
        }
    }

    /**
     * Возвращает текущую кодировку письма
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->mail->charset;
    }

    /**
     * Устанавливает кодировку письма
     *
     * @param string $charset
     * @return $this
     */
    public function setCharset($charset)
    {
        $this->mail->charset = $charset;
        return $this;
    }

    /**
     * Возвращает отправителя письма
     *
     * @return string|array
     */
    public function getFrom()
    {
        return $this->getAddressFromMail('from');
    }

    /**
     * Установка отправителя письма
     *
     * @param string|array $from
     * Вы можете передать массив, если отправка произволится несколькими отправителями.
     * Вы можете указать дополнительно имя отправителя в формате: [email => name]
     * @return $this
     */
    public function setFrom($from)
    {
        return $this->setAddressToMail('from', $from);
    }

    /**
     * Возвращает список получателей письма
     *
     * @return string|array
     */
    public function getTo()
    {
        return $this->getAddressFromMail('to');
    }

    /**
     * Устанавливает получателя письма
     *
     * @param string|array $to
     * Вы можете передать массив, если получателей несколько.
     * Вы можете указать дополнительно имя получателя в формате: [email => name]
     * @return $this
     */
    public function setTo($to)
    {
        return $this->setAddressToMail('to', $to);
    }

    /**
     * Возвращает адрес получателя ответа на письмо
     *
     * @return string|array
     */
    public function getReplyTo()
    {
        return $this->getAddressFromMail('reply_to');
    }

    /**
     * Устанавливает получателя ответа на письмо
     *
     * @param string|array $replyTo
     * Вы можете передать массив получателей ответа, если их должно быть несколько.
     * Вы можете дополнительно указать имя получателя ответа в формате: [email => name]
     * @return $this
     */
    public function setReplyTo($replyTo)
    {
        return $this->setAddressToMail('reply_to', $replyTo);
    }

    /**
     * Возвращает адрес получателя копии письма
     *
     * @return string|array
     */
    public function getCc()
    {
        return $this->getAddressFromMail('cc');
    }

    /**
     * Устанавливает получателя копии письма
     *
     * @param string|array $cc
     * Вы можете передать массив получателей копии письма, если их должно быть много.
     * Вы можете дополнительно указать имя получателя копии письма в формате: [email => name]
     * @return $this
     */
    public function setCc($cc)
    {
        return $this->setAddressToMail('cc', $cc);
    }

    /**
     * Возвращает адрес получателя скрытой копии письма
     *
     * @return array
     */
    public function getBcc()
    {
        return $this->getAddressFromMail('bcc');
    }

    /**
     * Устанавливает получателя скрытой копии письма
     *
     * @param string|array $bcc
     * Вы можете передать массив получателей скрытой копии письма, если их должно быть много.
     * Вы можете дополнительно указать имя получателя скрытой копии письма в фоормате: [email => name]
     * @return $this
     */
    public function setBcc($bcc)
    {
        return $this->setAddressToMail('bcc', $bcc);
    }

    /**
     * Возвращает тему письма
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->mail->subject;
    }

    /**
     * Устанавливает тему письма
     *
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->mail->subject = $subject;
        return $this;
    }

    /**
     * Устанавливает текстовую версию письма
     *
     * @param string $text
     * @return $this
     */
    public function setTextBody($text)
    {
        $this->mail->text = $text;
        return $this;
    }

    /**
     * Получение текстовой версии письма
     *
     * @return string
     */
    public function getTextBody()
    {
        return $this->mail->text;
    }

    /**
     * Устанавливает HTML версию пиьма
     *
     * @param string $html
     * @return $this
     */
    public function setHtmlBody($html)
    {
        $this->mail->html = $html;
        return $this;
    }

    /**
     * Получение HTML версии письма
     *
     * @return string
     */
    public function getHtmlBody()
    {
        return $this->mail->html;
    }

    /**
     * Прикрепляет файл к письму
     *
     * @param string $fileName Полное имя файла
     * @param array $options опции для файла. Валидные опции:
     * - fileName: Имя, отображаемое в письме
     * - contentType: MIME тип прикрепляемого файла
     *
     * @return $this
     */
    public function attach($fileName, array $options = [])
    {
        $attache = $this->buildAttache('at', $options);
        $attache->path = $fileName;
        if ($fileName = $this->attacheFile($attache)) {
            return $this->addAttachment($fileName, $options);
        }
        return $this;
    }

    /**
     * Прикрепление специфического контента как файла
     *
     * @param string $content
     * @param array $options опции для файла. Валидные опции:
     * - fileName: Имя, отображаемое в письме
     * - contentType: MIME тип прикрепляемого файла
     *
     * @return $this
     */
    public function attachContent($content, array $options = [])
    {
        $attache = $this->buildAttache('at', $options);
        $attache->content = $content;
        if ($fileName = $this->attacheFile($attache)) {
            return $this->addAttachment($fileName, $options);
        }
        return $this;
    }

    /**
     * Прикрепляет внедряемый объект и возвращет его CID.
     * Этот метод используется для внедрения изображения или любого другого объекта непосредственно в текст письма.
     *
     * @param string $fileName Полное имя файла
     * @param array $options опции для файла. Валидные опции:
     * - fileName: Имя, отображаемое в письме
     * - contentType: MIME тип прикрепляемого файла
     *
     * @return string
     */
    public function embed($fileName, array $options = [])
    {
        $attache = $this->buildAttache('em', $options);
        $attache->path = $fileName;
        if ($fileName = $this->attacheFile($attache)) {
            return $this->addEmbed($fileName, $options);
        }
        return $this;
    }

    /**
     * Прикрепляет специфический контент как внедряемый объект и возвращет его CID.
     * Этот метод используется для внедрения изображения или любого другого объекта непосредственно в текст письма.
     *
     * @param string $content Содержимое файла
     * @param array $options опции для файла. Валидные опции:
     * - fileName: Имя, отображаемое в письме
     * - contentType: MIME тип прикрепляемого файла
     *
     * @return string
     */
    public function embedContent($content, array $options = [])
    {
        $attache = $this->buildAttache('em', $options);
        $attache->content = $content;
        if ($fileName = $this->attacheFile($attache)) {
            return $this->addEmbed($fileName, $options);
        }
        return $this;
    }

    /**
     * Установка подписи к письму
     *
     * @param array|callable $signature Смотрите [[addSignature()]] для получения большей информации об объекте
     * @return $this
     * @throws InvalidConfigException
     */
    public function setSignature($signature)
    {
        $this->signs = [];
        return $this->addSignature($signature);
    }

    /**
     * Внедрение цифровой подписи к письму
     *
     * @param array|callable $signature подпись, может быть предоставлена как:
     * - callable, возвращающая конфигурационный массив
     * - конфигурационный массив для прикрепления подписи
     *
     * @return $this
     * @throws InvalidConfigException если передан не валидный массив
     */
    public function addSignature($signature)
    {
        if (is_callable($signature)) {
            $signature = call_user_func($signature);
        }
        if (!is_array($signature) || empty($signature['type'])) {
            throw new InvalidConfigException('Signature should be array configuration');
        }
        $this->signs[] = $signature;

        return $this;
    }

    /**
     * Добавление кастомного заголовка в отправляемое сообщение.
     * Можно использовать несколько раз для добавления нескольких значений в один заголовок
     *
     * @param string $name
     * @param string|array $value
     * @return $this
     */
    public function addHeader($name, $value)
    {
        if (!isset($this->headers[$name])) {
            $this->headers[$name] = [];
        }
        if (is_array($value)) {
            $this->headers[$name] = array_merge($this->headers[$name], $value);
        } else {
            $this->headers[$name][] = $value;
        }
        return $this;
    }

    /**
     * Устанавливает значение для кастомного заголовка письма
     *
     * @param string $name
     * @param string|array $value
     * @return $this
     */
    public function setHeader($name, $value)
    {
        if (isset($this->headers[$name])) {
            unset($this->headers[$name]);
        }
        return $this->addHeader($name, $value);
    }

    /**
     * Возвращает все значения для кастомного заголовка
     *
     * @param string $name
     * @return array
     */
    public function getHeader($name)
    {
        return isset($this->headers[$name])
            ? $this->headers[$name] : [];
    }

    /**
     * Установка нескольких заголовков одним массивом
     *
     * @param array $headers заголовки в формате: [name => value].
     * @return $this
     */
    public function setHeaders($headers)
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
        return $this;
    }

    /**
     * Устанавливает адрес возврата при отказа в отправке письма
     *
     * @param string $address
     * @return $this
     */
    public function setReturnPath($address)
    {
        $this->mail->return_path = $address;
        return $this;
    }

    /**
     * Возвращает адрес возврата при отказе в отправке письма
     *
     * @return string
     */
    public function getReturnPath()
    {
        return $this->mail->return_path;
    }

    /**
     * Установка приоритета для письма
     *
     * @param int $priority приоритет устанавливается в промежутке: `1..5`, где 1 наивысший приоритет, а 5 самый низкий
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->mail->priority = $priority;
        return $this;
    }

    /**
     * Возвразает приоритет письма
     *
     * @return int значение в промежутке: `1..5`, где 1 наивысший приоритет, а 5 самый низкий
     */
    public function getPriority()
    {
        return $this->mail->priority;
    }

    /**
     * Устанавливает получателя оповещения о прочтении
     *
     * @param string|array $addresses
     * @return $this
     */
    public function setReadReceiptTo($addresses)
    {
        $this->mail->read_receipt_to = $addresses;
        return $this;
    }

    /**
     * Возвращает адрес получателя оповещения о прочтении
     *
     * @return string|array
     */
    public function getReadReceiptTo()
    {
        return $this->mail->read_receipt_to;
    }

    /**
     * Отправка письма в очередь
     *
     * @param MailerInterface $mailer
     * @return bool
     * @throws InvalidConfigException
     */
    public function send(MailerInterface $mailer = null)
    {
        if ($mailer === null && $this->mailer === null) {
            throw new InvalidConfigException('Некорректно настроена отправка письма.');
        }
        if ($this->saveMail()) {
            if ($mailer === null) {
                $mailer = $this->mailer;
            }
            return $mailer->send($this);
        }
        return false;
    }

    /**
     * Возвращает строковое представление письма
     *
     * @return string
     * @throws InvalidConfigException
     */
    public function toString()
    {
        return $this->getSwiftMessage()->toString();
    }

    /**
     * Генерация Swift сообщения для отправки с переносом всех данных письма
     *
     * @return \yii\swiftmailer\Message
     * @throws InvalidConfigException
     */
    public function getSwiftMessage()
    {
        $message = new \yii\swiftmailer\Message();

        $methods = [
            'charset', 'from', 'to', 'replyTo', 'cc', 'bcc', 'subject',
            'textBody', 'htmlBody', 'returnPath', 'priority', 'readReceiptTo'
        ];
        foreach ($methods as $method) {
            $set = 'set' . ucfirst($method);
            $get = 'get' . ucfirst($method);
            if ($data = $this->{$get}()) {
                $message->{$set}($data);
            }
        }
        foreach ($this->signs as $sign) {
            $message->addSignature($sign);
        }
        foreach ($this->attachments as $attachment) {
            $message->attach($attachment['name'], $attachment['options']);
        }
        foreach ($this->embeds as $embed) {
            $message->getSwiftMessage()->embed($this->createSwiftEmbed($embed));
        }
        foreach ($this->headers as $name => $values) {
            $message->setHeader($name, $values);
        }

        return $message;
    }

    /**
     * Создание встраиваемого содержимого
     *
     * @param array $embed
     * @return \Swift_Mime_EmbeddedFile
     */
    protected function createSwiftEmbed($embed)
    {
        $embedFile = \Swift_EmbeddedFile::fromPath($embed['name']);
        if (!empty($embed['options']['fileName'])) {
            $embedFile->setFilename($embed['options']['fileName']);
        }
        if (!empty($embed['options']['contentType'])) {
            $embedFile->setContentType($embed['options']['contentType']);
        }
        $embedFile->setId($embed['id']);
        return $embedFile;
    }

    /**
     * Получение десериализованного поля от модели почтового сообщения
     *
     * @param string $key
     * @return mixed
     */
    protected function getAddressFromMail($key)
    {
        return $this->unserialize($this->mail->{$key});
    }

    /**
     * Установка в почтовую модель сериализованного поля
     *
     * @param string $key
     * @param array|string $data
     * @return $this
     */
    protected function setAddressToMail($key, $data)
    {
        $data = $this->normalizeMailboxes($data);
        $data = $this->serialize($data);
        $this->mail->{$key} = $data;
        return $this;
    }

    /**
     * Сериализация данных
     *
     * @param array|string $data
     * @return string
     */
    protected function serialize($data)
    {
        if (empty($data)) {
            $data = [];
        }
        return serialize($data);
    }

    /**
     * Десериализация
     *
     * @param string $data
     * @return array|string
     */
    protected function unserialize($data)
    {
        if (empty($data)) {
            return [];
        }
        return unserialize($data);
    }

    /**
     * Создание объекта вложения
     *
     * @param string $prefix
     * @param array $options
     * @return Attachment
     */
    protected function buildAttache($prefix = '', $options = [])
    {
        $attache = new Attachment();
        $attache->prefix = $prefix;
        $attache->message = $this->messageId;
        if (!empty($options['fileName'])) {
            $attache->name = $options['fileName'];
        }
        return $attache;
    }

    /**
     * Сохранение файла
     *
     * @param Attachment $file Мета информация о файле
     * @return string|null Пусть до сохраненного файла
     */
    protected function attacheFile(Attachment $file)
    {
        /** @var AttacheStorage $attache */
        if ($attache = \Yii::$app->get($this->attacheComponent, false)) {
            return $attache->save($file);
        }
        // Если не настроен компонент обработки файлов, просто прикрепляем файлы на диске.
        // Это исключает возможность прикрепить содержимое как файл.
        if (!empty($file->path) && is_file($file->path)) {
            return $file->path;
        }
        throw new InvalidParamException("Невозможно прикрепить файл.");
    }

    /**
     * Добавление файла в список прикрепляемых
     *
     * @param string $fileName
     * @param array $options
     * @return $this
     */
    protected function addAttachment($fileName, $options = [])
    {
        $this->attachments[] = [
            'name' => $fileName,
            'options' => $options,
        ];
        return $this;
    }

    /**
     * Добавление файла в список внедренных
     *
     * @param string $fileName
     * @param array $options
     * @return string
     */
    protected function addEmbed($fileName, $options = [])
    {
        $id = $this->getRandomId();
        $this->embeds[] = [
            'id' => $id,
            'name' => $fileName,
            'options' => $options,
        ];
        return 'cid:' . $id;
    }

    /**
     * Сохранение письма для очереди
     *
     * @return bool
     */
    protected function saveMail()
    {
        $mail = $this->mail;

        $mail->attachments = $this->serialize($this->attachments);
        $mail->headers = $this->serialize($this->headers);
        $mail->embeds = $this->serialize($this->embeds);
        $mail->signs = $this->serialize($this->signs);

        if ($mail->save()) {
            $this->id = $mail->id;
            return true;
        }

        return false;
    }

    /**
     * Swift mailer вариация генерации id для внедряемого объекта
     *
     * @return string
     */
    protected function getRandomId()
    {
        $idLeft = md5(getmypid() . '.' . time() . '.' . uniqid(mt_rand(), true));
        return $idLeft.'@chulakov.generated';
    }

    /**
     * Нормализация хранения почтовых адерсов
     *
     * @param string[] $mailboxes
     * @return string[]
     */
    protected function normalizeMailboxes($mailboxes)
    {
        $actualMailboxes = [];

        if (!is_array($mailboxes)) {
            $mailboxes = (array)$mailboxes;
        }
        foreach ($mailboxes as $address => $name) {
            if (!is_string($address)) {
                $address = $name;
                $name = null;
            }
            $actualMailboxes[$address] = $name;
        }

        return $actualMailboxes;
    }
}
