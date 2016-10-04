<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Listofgoods;

/**
 * ListofgoodsSearch represents the model behind the search form about `backend\models\Listofgoods`.
 */
class ListofgoodsSearch extends Listofgoods
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['list_id', 'orders_order_id', 'good_count'], 'integer'],
            [['goods_good_1c_id'],'string'],
            [['goods_good_1c_id'], 'safe'],
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
    public function search($params,$order_id = 0)
    {
        if($order_id == 0){
            $query = Listofgoods::find();
        }else{
            $query = Listofgoods::find()->where(['orders_order_id'=>$order_id]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
        
        $query->joinWith('goodsGoodId');

        $query->andFilterWhere([
            //'list_id' => $this->list_id,
            'orders_order_id' => $this->orders_order_id,
            'good_count' => $this->good_count,
        ])
                ->andFilterWhere(['like', 'goods.good_name', $this->goods_good_1c_id]);
        
        return $dataProvider;
    }
}
