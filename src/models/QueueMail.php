<?php
/**
 * Файл класса QueueMail
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\queuemailer\models;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * Модель полей письма для отложенной отправки
 *
 * @property string $id
 * @property string $to
 * @property string $from
 * @property string $subject
 * @property integer $priority
 * @property string $text
 * @property string $html
 * @property string $charset
 * @property string $reply_to
 * @property string $cc
 * @property string $bcc
 * @property string $return_path
 * @property string $read_receipt_to
 * @property string $attachments
 * @property string $embeds
 * @property string $signs
 * @property string $headers
 *
 * @property string $created_at
 * @property string $created_by
 */
class QueueMail extends ActiveRecord implements MailStorageInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%queue_mail}}';
    }

    /**
     * Поиск модели по ее ID
     *
     * @param integer $id
     * @return static|null
     */
    public static function findById($id)
    {
        return static::findOne($id);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => 'created_at',
                ],
            ],
            [
                'class' => BlameableBehavior::className(),
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => 'created_by',
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['priority', 'integer', 'max' => 5],
            [[
                'subject', 'charset', 'return_path', 'read_receipt_to'
            ], 'string', 'max' => 255],
            [[
                'text', 'html', 'reply_to', 'cc', 'bcc',
                'attachments', 'embeds', 'signs', 'headers'
            ], 'safe'],
        ];
    }
}
