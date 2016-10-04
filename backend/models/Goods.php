<?php

namespace backend\models;

use Yii;
use backend\models\Typeprice;
use backend\models\Basket;

/**
 * This is the model class for table "goods".
 *
 * @property integer $good_1c_id
 * @property integer $good_id
 * @property string $good_name
 * @property string $hash_id
 * @property string $good_detail_guid
 * @property string $good_description
 * @property string $good_logo
 * @property integer $good_price
 * @property integer $typeprices_id
 */
class Goods extends \yii\db\ActiveRecord
{
    public $file;
    public $img_title;
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
            [['good_price','typeprices_id'], 'integer'],
            [['good_name'], 'string', 'max' => 200],
            [['good_logo'], 'string', 'max' => 100],
            [['good_detail_guid'], 'string', 'max' => 36],
            [['good_1c_id','hash_id'], 'string', 'max' => 11],
            [['file'],'file', 'extensions' => ['jpeg','jpg'], 'checkExtensionByMimeType'=>false, 'skipOnEmpty'=>true],
            ['img_title', 'string', 'max' => 500]
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
            'typeprices_id' => 'Тип цены',
            'file' => 'Файл:',
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
     *
     *
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
}
