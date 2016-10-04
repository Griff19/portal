<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "type_price".
 *
 * @property integer $type_price_id
 * @property string $type_price_name
 */
class Typeprice extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'type_price';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type_price_name'], 'required'],
            [['type_price_name'], 'string', 'max' => 9]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'type_price_id' => 'Type Price ID',
            'type_price_name' => 'Type Price Name',
        ];
    }
}
