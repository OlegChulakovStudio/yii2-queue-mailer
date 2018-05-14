<?php
/**
 * Файл класса AttacheStorage
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\queuemailer;

use chulakov\queuemailer\models\Attachment;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\FileHelper;

class AttacheStorage extends Component
{
    /**
     * @var bool Необходимость сохранять все файлы во временный каталог для отправки
     */
    public $storageAll = false;
    /**
     * @var bool Необходимость очистки файлов во временном каталоге хранилища
     */
    public $storageClear = true;
    /**
     * @var string Путь для временных файлов
     */
    public $storagePath = '@runtime/attachments';

    /**
     * @throws \yii\base\Exception
     */
    public function init()
    {
        parent::init();
        $this->storagePath = \Yii::getAlias($this->storagePath);
        if (!is_dir($this->storagePath)) {
            FileHelper::createDirectory($this->storagePath);
        }
    }

    /**
     * Сохранение файла
     *
     * @param Attachment $attachment
     * @return string
     */
    public function save(Attachment $attachment)
    {
        $result = true;
        $fullPath = implode(DIRECTORY_SEPARATOR, array_filter([
            $this->storagePath, $attachment->message, $attachment->getName()
        ]));
        if ($attachment->content) {
            $result = $this->putFile($fullPath, $attachment->content);
        } elseif ($this->storageAll) {
            $result = $this->copyFile($attachment->path, $fullPath);
        } else {
            $fullPath = $attachment->path;
        }
        if ($result) {
           return $fullPath;
        }
        return null;
    }

    /**
     * Очистка файлов из рантайма
     *
     * @param array $files
     */
    public function removeFiles($files)
    {
        if (!$this->storageClear) {
            return;
        }
        foreach ($files as $file) {
            if (strpos($this->storagePath, $file['name']) !== false) {
                @unlink($file['name']);
            }
        }
    }

    /**
     * Запись содержимого в файл
     *
     * @param string $path
     * @param string $content
     * @return bool
     */
    protected function putFile($path, $content)
    {
        if ($this->checkDirectory($path)) {
            return !!file_put_contents($path, $content);
        }
        return false;
    }

    /**
     * Копирование файла
     *
     * @param string $from
     * @param string $to
     * @return bool
     */
    protected function copyFile($from, $to)
    {
        if (!is_file($from)) {
            return false;
        }
        if ($this->checkDirectory($to)) {
            return copy($from, $to);
        }
        return false;
    }

    /**
     * Проверка и создание отсутствующей директории
     *
     * @param string $path
     * @return bool
     */
    protected function checkDirectory($path)
    {
        try {
            $dir = dirname($path);
            if (!is_dir($dir)) {
                return FileHelper::createDirectory($dir);
            }
        } catch (Exception $e) {
            \Yii::error($e);
        }
        return false;
    }
}
