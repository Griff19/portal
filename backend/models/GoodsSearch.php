<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
//use backend\models\Goods;

/**
 * GoodsSearch represents the model behind the search form about `backend\models\Goods`.
 */
class GoodsSearch extends Goods
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['good_id', 'good_price','typeprices_id', 'status'], 'integer'],
            [['good_1c_id','good_name', 'good_detail_guid', 'good_description'], 'safe'],
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
     * Отбираем данные для вывода товаров
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params, $tp = 0)
    {
        //if(Yii::$app->user->can('operatorSQL')){
        if ($tp == 0 ){
           $query = Goods::find();
        } else {
            $query = Goods::find()->where(['>', 'status', Goods::DISABLE])->andWhere(['typeprices_id' => $tp]);
            $query->joinWith('currentNom', true, 'RIGHT JOIN')->andWhere('guid_1c IS NOT NULL');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['good_name'=> SORT_ASC, 'good_id' => SORT_ASC]],
            'pagination' => ['pageSize' => 40],
        ]);

        //$dataProvider->pagination = FALSE;
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        $query->joinWith('tPname');
        $query->andFilterWhere([
            'good_id' => $this->good_id,
            'good_price' => $this->good_price,
            'good_detail_guid' => $this->good_detail_guid,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['ilike', 'good_name', $this->good_name])
            ->andFilterWhere(['like', 'good_1c_id', $this->good_1c_id])
            ->andFilterWhere(['like', 'good_logo', $this->good_logo])
            ->andFilterWhere(['ilike', 'good_description', $this->good_description])
            ->andFilterWhere(['like','type_price.type_price_name', $this->typeprices_id]);

        return $dataProvider;
    }
}
