<?php

namespace backend\models;
use yii\db\ActiveRecord;

use Yii;

/**
 * This is the model class for table "basket".
 *
 * @property integer $id
 * @property integer $good_id
 * @property integer $count
 * @property integer $summ
 */
class Basket extends ActiveRecord
{
    public $good_1c;
    public $order_date;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'basket';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id','good_id', 'count', 'summ'], 'required'],
            [['user_id', 'count', 'summ'], 'integer'],
            [['good_id'], 'string', 'max' => 11],
            [['good_1c'],'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_date' => 'Дата отгрузки',
            'id' => 'Ид строки',
            'good_1c' => 'Код товара',
            'good_id' => 'Наименование',
            'count' => 'Количество',
            'summ' => 'Сумма',
        ];
    }
    //Получаем количество по столбцу
    public function getTotals($field)
    {
        $command = Yii::$app->db->createCommand('SELECT sum('. $field .') as res FROM basket WHERE user_id =' . Yii::$app->user->id);
        $result = $command->queryAll();
        
        return $result[0]['res'];
    }

    //Получаем количество строк в корзине...
    public static function getCount()
    {
        $searchModel = new BasketSearch();
        $dataProvider = $searchModel->search(['user_id' => Yii::$app->user->id]);
        return $dataProvider->totalCount;        
    }
    //Привязываем таблицу товаров...
    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['hash_id' => 'good_id']);
    }
}
