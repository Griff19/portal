<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Customers;

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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        //каждому пользователю соответствуют его контрагенты
        if(Yii::$app->user->can('admin')){
            $query = Customers::find();
        } else {
            $query = Customers::find()->where(['user_id'=>Yii::$app->user->id]);
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
            'customer_id' => $this->customer_id,
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
