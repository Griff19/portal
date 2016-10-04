<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Actstable;

/**
 * ActstableSearch represents the model behind the search form about `backend\models\ActsTable`.
 */
class ActstableSearch extends Actstable
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'acts_id', 'beg_sald', 'end_sald'], 'integer'],
            [['act_num', 'date_doc', 'num_doc', 'name_doc', 'cod_good', 'actstable_comm'], 'safe'],
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
    public function search($params, $act_num = 0)
    {
        $query = ActsTable::find()->where(['act_num' => $act_num]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['date_doc' => SORT_ASC]],
        ]);
        
        $dataProvider->setPagination(['defaultPageSize' => 50]);
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'acts_id' => $this->acts_id,
            'date_doc' => $this->date_doc,
            'beg_sald' => $this->beg_sald,
            'end_sald' => $this->end_sald,
        ]);

        $query->andFilterWhere(['like', 'act_num', $this->act_num])
            ->andFilterWhere(['like', 'num_doc', $this->num_doc])
            ->andFilterWhere(['like', 'name_doc', $this->name_doc])
            ->andFilterWhere(['like', 'cod_good', $this->cod_good])
            ->andFilterWhere(['like', 'actstable_comm', $this->actstable_comm]);

        return $dataProvider;
    }
}
