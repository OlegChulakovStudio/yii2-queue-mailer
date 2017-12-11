<?php
/**
 * Файл класса MessageJobInterface
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\queuemailer\jobs;

use chulakov\queuemailer\Mailer;
use chulakov\queuemailer\Message;

interface MessageJobInterface
{
    /**
     * Создание объекта с данными для очереди
     *
     * @param Message $message
     * @param Mailer $mailer
     * @return MessageJobInterface
     */
    public static function create($message, $mailer);
}
