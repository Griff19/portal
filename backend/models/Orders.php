<?php

namespace backend\models;

/**
 * Модель для таблицы Заказы "orders".
 *
 * @property integer $order_id
 * @property string $order_timestamp
 * @property integer $customers_customer_id
 * @property integer $order_amount
 * @property integer $user_id
 * @property string $status
 * @property string $order_date
 * @property Listofgoods[] $listofgoods
 * @property Customers $customersCustomer
 */
class Orders extends \yii\db\ActiveRecord
{
	const SCENARIO_SAFE = 'safe';

	const STATUS_CREATE = 'Черновик';
	const STATUS_PLACE = 'Размещен';
	const STATUS_PROCESSED = 'Обработан';
	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_amount', 'order_timestamp'], 'safe'],
            //[['order_date'], 'date', 'format' => 'php:Y.m.d'],
            [['customers_customer_id', 'order_amount', 'user_id', 'order_date'], 'required'],
            [['order_date'], 'compare', 'compareValue' => date('Y-m-d'), 'operator' => '>', 'message' => '{attribute} должна быть в будущем'],
            [['customers_customer_id', 'user_id'], 'integer'],
            [['status'],'string']
        ];
    }

    public function scenarios() {
	    $scenarios                      = parent::scenarios();
	    $scenarios[self::SCENARIO_SAFE] = ['order_amount', 'order_timestamp', 'customers_customer_id', 'user_id', 'status'];
	    return $scenarios;
    }

	/**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_id' => 'Номер заказа',
            'order_timestamp' => 'Время создания',
            'order_date' => 'Дата отгрузки',
            'customers_customer_id' => 'Контрагент',
            'order_amount' => 'Сумма',
            'user_id' => 'Пользователь',
            'status' => 'Состояние заказа'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getListofgoods()
    {
        return $this->hasMany(Listofgoods::className(), ['orders_order_id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomersCustomer()
    {
        return $this->hasOne(Customers::className(), ['customer_id' => 'customers_customer_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsersUser(){
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
