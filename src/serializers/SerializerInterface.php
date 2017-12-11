<?php
/**
 * Файл класса SerializerInterface
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\queuemailer\serializers;

interface SerializerInterface
{
    /**
     * Метод сериализации
     *
     * @param array $data
     * @return string
     */
    public function serialize($data);

    /**
     * Метод десериализации
     *
     * @param string $data
     * @return array|null
     */
    public function unserialize($data);
}
