<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "typical_order".
 *
 * @property integer $id
 * @property integer $customer_id
 * @property integer $good_id
 * @property integer $count
 * @property Goods $good
 */
class TypicalOrder extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'typical_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'good_id'], 'required'],
            [['customer_id', 'good_id', 'count'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Customer ID',
            'good_id' => 'Good ID',
            'count' => 'Count',
        ];
    }

    /**
     * Связываем с моделью Товары
     */
    public function getGood(){
        return $this->hasOne(Goods::className(), ['good_id' => 'good_id']);
    }
}
