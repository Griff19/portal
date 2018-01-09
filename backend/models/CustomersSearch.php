<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Customers;
use backend\models\Orders;

/**
 * CustomersSearch represents the model behind the search form about `backend\models\Customers`.
 */
class CustomersSearch extends Customers
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id'], 'integer'],
            [['user_id','typeprices_id','customer_1c_id'],'string'],
            [['customer_name', 'customer_email'], 'safe'],
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
     * Выбираем данные по контрагентам для контроллера
	 * Каждому пользователю соответствуют его контрегенты
	 * Телефонный оператор видит всех активных контрегентов у которых указаны телефоны и у них нет размещенных заказов
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        if(Yii::$app->user->can('telephone')){
            $query = Customers::find()->where('phone IS NOT NULL')->andWhere(['customers.status' => Customers::STATUS_ACTIVE])
				//->andWhere("orders.status <> :status OR orders.status IS NULL", [':status' => Orders::STATUS_PLACE])
				->orderBy('sort')
	            ->joinWith(['phone', 'orders']);
        } else {
            $query = Customers::find()->where(['user_id'=>Yii::$app->user->id])
				->andWhere(['customers.status' => Customers::STATUS_ACTIVE]);
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
        $query->joinWith('usersUser');
        $query->joinWith('typePrices');
        
        $query->andFilterWhere([
            'customers.customer_id' => $this->customer_id,
            //'user_id' => $this->user_id,
            //'typeprices_id' => $this->typeprices_id,
        ]);

        $query->andFilterWhere(['like', 'customer_name', $this->customer_name])
            ->andFilterWhere(['like', 'user.fullname', $this->user_id])
            ->andFilterWhere(['like','type_price.type_price_name', $this->typeprices_id])
            ->andFilterWhere(['like', 'customer_email', $this->customer_email]);

        return $dataProvider;
    }
}
