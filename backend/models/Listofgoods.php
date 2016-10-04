<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "listofgoods".
 *
 * @property integer $list_id
 * @property integer $orders_order_id
 * @property integer $goods_good_1c_id
 * @property integer $good_count
 */
class Listofgoods extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'listofgoods';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['orders_order_id', 'goods_good_1c_id', 'good_count'], 'required'],
            [['orders_order_id', 'good_count'], 'integer'],
            [['goods_good_1c_id'],'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'list_id' => 'List ID',
            'orders_order_id' => 'Номер заказа',
            'goods_good_1c_id' => 'Наименование',
            'good_count' => 'Количество',
        ];
    }
    //привязываем таблицу товаров 
    public function getGoodsGoodId(){
        return $this->hasOne(Goods::className(),['hash_id' => 'goods_good_1c_id']);
    }
    
}
