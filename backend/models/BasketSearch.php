<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Basket;

/**
 * BasketSearch represents the model behind the search form about `backend\models\Basket`.
 */
class BasketSearch extends Basket
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'count'], 'integer'],
            [['good_id', 'good_1c'], 'string'],
            ['summ','safe'],
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
        $query = Basket::find()->where(['user_id' => Yii::$app->user->id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
	    'sort' => ['defaultOrder' => ['id' => SORT_ASC]]		    	
        ]);
        //var_dump($params);
        //die();
        
        
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        $query->joinWith('goods');
        $query->andFilterWhere([
            'id' => $this->id,
            //'good_id' => $this->good_id,
            //'goods.good_1c_id' => $this->good_1c,
            'count' => $this->count,
            'summ' => $this->summ * 100 == 0 ? '' : $this->summ * 100,
        ])
                ->andFilterWhere(['like', 'goods.good_name', $this->good_id])
                ->andFilterWhere(['like', 'goods.good_1c_id', $this->good_1c]);

        return $dataProvider;
    }
}
