<?php

namespace backend\models;

use Yii;
use common\models\User;
use backend\models\Customers;
use yii\base\Exception;
use yii\base\ExitException;

/**
 * This is the model class for table "scans".
 *
 * @property integer $scan_id
 * @property string $scan_name
 * @property integer $user_id
 * @property integer $customer_id
 * @property string $path
 */
class Scans extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public $file;

    public static function tableName()
    {
        return 'scans';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'customer_id', 'path'], 'required'],
            [['user_id', 'customer_id'], 'integer'],
            [['file'],'file',
                'extensions' => ['jpg','png'],
                'checkExtensionByMimeType'=>false,
                
            ],
            [['path','scan_name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            //'scan_id' => 'Scan ID',
            'scan_name' => 'Наименование документа',
            'user_id' => 'Пользователь',
            'customer_id' => 'Контрагент',
            'path' => 'Изображение',
        ];
    }



    /**
     * Функция для изменения размера изображения, будем использовать для валидации загружаемых пользователем
     * изображений
     * @param $outfile
     * @param $infile
     * @param $percents
     * @param $quality
     * @return bool
     */
    public function imageresize($outfile,$infile,$percents,$quality) {
        try {
            $im = imagecreatefromjpeg($infile);
        } catch (\ErrorException $e){
            Yii::$app->session->setFlash('error','Загружена не картинка');
            return false;
        }
            $w = imagesx($im) * $percents / 100;
            $h = imagesy($im) * $percents / 100;
            $im1 = imagecreatetruecolor($w, $h);
            imagecopyresampled($im1, $im, 0, 0, 0, 0, $w, $h, imagesx($im), imagesy($im));

            imagejpeg($im1, $outfile, $quality);
            imagedestroy($im);
            imagedestroy($im1);
            return true;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsersUser(){
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomersCustomer(){
        return $this->hasOne(Customers::className(), ['customer_id' => 'customer_id']);
    }
}
