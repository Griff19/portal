<?php

namespace backend\models;
use yii\db\ActiveRecord;

use Yii;

/**
 * Модель для таблицы "Корзина" - "basket".
 *
 * @property integer $id
 * @property string $good_id
 * @property integer $user_id
 * @property integer $count
 * @property integer $summ
 * @property Goods $goods
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
            [['good_1c'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_date' => 'Дата отгрузки',
            'id' => 'ID',
            'good_1c' => 'Код товара',
            'good_id' => 'Наименование',
            'count' => 'Количество',
            'summ' => 'Сумма',
        ];
    }

	/**
	 * Получаем сумму по столбцу
	 * @param string $field
	 * @param null $customer_id
	 *
	 * @return mixed
	 */
    public static function getTotals($field, $customer_id = null)
    {
	    if ($customer_id)
	        $sum = Basket::find()->where(['user_id' => $customer_id])->sum($field);
	    else
		    $sum = Basket::find()->where(['user_id' => Yii::$app->user->id])->sum($field);

	    return $sum / 100;
    }

    //Получаем количество строк в корзине...
    public static function getCount()
    {
        $searchModel = new BasketSearch();
        $dataProvider = $searchModel->search(['user_id' => Yii::$app->user->id]);
        return $dataProvider->totalCount;        
    }

    /** Связываем с моделью Товаров */
    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['hash_id' => 'good_id']);
    }
}
