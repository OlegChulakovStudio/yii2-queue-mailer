<?php
/**
 * Файл класса Attachment
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\queuemailer\models;

class Attachment
{
    /**
     * @var string Желаемое имя файла
     */
    public $name;
    /**
     * @var string Полный путь до прикрепляемого файла
     */
    public $path;
    /**
     * @var string Содержимое файла (если его нет на диске)
     */
    public $content;
    /**
     * @var string Префикс перед генерируемым именем файла
     */
    public $prefix = '';
    /**
     * @var string Индентификатор сообщения
     */
    public $message = '';

    /**
     * Имя сохраняемого файла
     *
     * @return string
     */
    public function getName()
    {
        if (!empty($this->name)) {
            return $this->name;
        }
        if (is_file($this->path)) {
            return basename($this->path);
        }
        return uniqid($this->prefix, true);
    }
}
