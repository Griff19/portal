<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\TypePrice;

/**
 * TypePriceSearch represents the model behind the search form about `backend\models\TypePrice`.
 */
class TypePriceSearch extends TypePrice
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type_price_id'], 'integer'],
            [['type_price_name'], 'safe'],
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
        $query = TypePrice::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'type_price_id' => $this->type_price_id,
        ]);

        $query->andFilterWhere(['like', 'type_price_name', $this->type_price_name]);

        return $dataProvider;
    }
}
