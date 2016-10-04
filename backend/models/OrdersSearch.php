<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
//use backend\models\Orders;
use yii\web\ForbiddenHttpException;

/**
 * OrdersSearch represents the model behind the search form about `backend\models\Orders`.
 */
class OrdersSearch extends Orders
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id'], 'integer'],
            [['status'],'string'],
            [['order_timestamp', 'customers_customer_id', 'user_id','order_amount'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        if(Yii::$app->user->can('admin')){
            $query = Orders::find();
        } else {
            $query = Orders::find()->where(['orders.user_id'=>Yii::$app->user->id]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['order_timestamp' => SORT_DESC]]
        ]);
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        $query->joinWith('customersCustomer');
        if(Yii::$app->user->can('admin')){
            $query->joinWith('usersUser');
        }
        $query->andFilterWhere([
            'order_id' => $this->order_id,
            'order_timestamp' => $this->order_timestamp,
            //'order_amount' => $this->order_amount,
            //'user_id' => $this->user_id,
            //'status' => $this->status,
        ])
        ->andFilterWhere(['like','customers.customer_name',$this->customers_customer_id])
        ->andFilterWhere(['like','user.username',$this->user_id])
        ->andFilterWhere(['like','orders.status',$this->status]);

        return $dataProvider;
    }

    /**
     * Поиск размещенных заказов
     *
     * @param array $params
     * @return ActiveDataProvider
     * @throws ForbiddenHttpException
     */
    public function searchForfile($params)
    {
        if(Yii::$app->user->can('operatorSQL')){
            $query = Orders::find()->where(['orders.status' => 'Размещен']);
        } else {
            throw new ForbiddenHttpException('Только оператор базы и администратор могут выгружать данные');
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        $query->joinWith('customersCustomer');
        if(Yii::$app->user->can('admin')){
            $query->joinWith('usersUser');
        }
        $query->andFilterWhere([
            'order_id' => $this->order_id,
            'order_timestamp' => $this->order_timestamp,
            'order_amount' => $this->order_amount,
            //'user_id' => $this->user_id,
            //'status' => $this->status,
        ])
        ->andFilterWhere(['like','customers.customer_name',$this->customers_customer_id])
        ->andFilterWhere(['like','user.username',$this->user_id])
        ->andFilterWhere(['like','orders.status',$this->status]);

        return $dataProvider;
    }
}
