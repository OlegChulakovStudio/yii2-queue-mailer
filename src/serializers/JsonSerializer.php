<?php
/**
 * Файл класса JsonSerializer
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\queuemailer\serializers;

use yii\helpers\Json;

class JsonSerializer implements SerializerInterface
{
    /**
     * @var int Опции сериализации
     */
    public $options = 320;

    /**
     * Метод сериализации
     *
     * @param array $data
     * @return string
     */
    public function serialize($data)
    {
        return Json::encode($data, $this->options);
    }

    /**
     * Метод десериализации
     *
     * @param string $data
     * @return array|null
     */
    public function unserialize($data)
    {
        return Json::decode($data, true);
    }
}