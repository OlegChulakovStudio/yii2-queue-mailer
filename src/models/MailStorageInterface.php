<?php
/**
 * Файл класса MailStorageInterface
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\queuemailer\models;

/**
 * Интерфейс модели хранения данных письма
 *
 * @package chulakov\queuemailer\models
 */
interface MailStorageInterface
{
    /**
     * Поиск модели по ее ID
     *
     * @param integer $id
     * @return static|null
     */
    public static function findById($id);
}