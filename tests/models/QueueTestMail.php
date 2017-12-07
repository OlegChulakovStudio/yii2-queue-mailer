<?php
/**
 * Файл класса QueueMail
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\queuemailer\tests\models;

use chulakov\queuemailer\models\MailStorageInterface;
use yii\base\BaseObject;

class QueueTestMail extends BaseObject implements MailStorageInterface
{
    public $id;
    public $to;
    public $from;
    public $subject;
    public $priority;
    public $text;
    public $html;
    public $charset;
    public $reply_to;
    public $cc;
    public $bcc;
    public $return_path;
    public $read_receipt_to;
    public $attachments;
    public $embeds;
    public $signs;
    public $headers;
    public $created_at;
    public $created_by;

    public static function findById($id)
    {
        return new static([
            'id' => $id,
        ]);
    }
}
