<?php

namespace backend\models;

use Yii;

/**
 * Модель товаров, соответствует таблице "goods"
 *
 * @property integer $good_1c_id
 * @property integer $good_id
 * @property string $good_name
 * @property string $hash_id
 * @property string $good_detail_guid
 * @property string $good_description
 * @property string $good_logo
 * @property string $good_info
 * @property integer $good_price
 * @property float $good_price_real
 * @property integer $typeprices_id
 * @property integer $status
 * @property string $hash
 * @property Images $image
 */
class Goods extends \yii\db\ActiveRecord
{
    public $file;
    public $img_title;
    public $good_price_real;
    public $discount;

    const DISABLE = 0;
    const ENABLE = 1;
    const DISCOUNT = 2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'goods';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['good_name', 'good_price','good_1c_id', 'good_detail_guid'], 'required'],
            ['good_price_real', 'number'],
            [['good_logo', 'good_price','typeprices_id', 'status'], 'integer'],
            [['good_name'], 'string', 'max' => 200],
            [['good_detail_guid', 'hash'], 'string', 'max' => 36],
            [['good_1c_id','hash_id'], 'string', 'max' => 11],
            [['file'],'file', 'extensions' => ['jpeg','jpg'], 'checkExtensionByMimeType'=>false, 'skipOnEmpty'=>true],
            ['img_title', 'string', 'max' => 500],
            ['discount', 'boolean']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'good_1c_id' => 'Код в 1С',
            'good_detail_guid' => 'Код признака',
            'good_description' => 'Свойства',
            'good_id' => 'Ид товара',
            'good_name' => 'Наименование',
            'good_logo' => 'Изображение',
            'good_price' => 'Цена',
            'good_price_real' => 'Цена',
            'good_info' => 'Подробности',
            'typeprices_id' => 'Тип цены',
            'file' => 'Файл:',
            'discount' => 'Акция',
            'status' => 'Статус',
        ];
    }

     /**
     * Связываем с моделью типов цен
     */
    public function getTPname()
    {
        return $this->hasOne(Typeprice::className(), ['type_price_id' => 'typeprices_id']);
    }

    /**
     * Связываем таблицу товаров с "корзиной" чтобы получать количество лежащих в "корзине".
     */
    public function getBasketCount()
    {
        $basket = Basket::find()->where(['good_id' => $this->hash_id, 'user_id' => Yii::$app->user->id])->one();
        if (isset($basket))
        {
            return $basket->count;
        } else {
            return 0;
        }
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return 'goods' . $this->hash_id;
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(Images::className(), ['id' => 'good_logo']);
    }
}
