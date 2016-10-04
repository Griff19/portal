<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 28.05.2016
 * Time: 21:21
 */

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Class Emails
 * @property integer id
 * @property string attach
 * @property string receiver_email
 * @property string subject
 * @property mixed content_email
 * @package backend\models
 */
class Emails extends ActiveRecord
{

    public static function tableName(){
        return 'emails';
    }

    public function rules(){
        return [
            [['receiver_email', 'subject'], 'required'],
            [['receiver_name', 'receiver_email', 'subject'], 'string', 'max' => 255],
            ['attach', 'file'],
            ['content_email', 'string']
        ];
    }

    public function attributeLabels(){
        return [
            'receiver_name' => 'Имя получателя',
            'receiver_email' => 'Email получателя',
            'subject' => 'Тема письма',
            'content_email' => 'Содержание письма',
            'attach' => 'Вложение'
        ];
    }

    /**
     * Отправляем письмо
     * @param $email
     * @param $sub
     * @param $body
     */
    public static function sendMail($email, $sub, $body) {
        Yii::$app->mailer->compose()
            ->setFrom(['portal@altburenka.ru' => 'Портал-Алтайская Буренка'])
            ->setTo($email)
            ->setSubject($sub)
            ->setHtmlBody($body)
            ->send();
    }
}