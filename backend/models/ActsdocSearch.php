<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\ActsDoc;

/**
 * ActsdocSearch represents the model behind the search form about `backend\models\ActsDoc`.
 */
class ActsdocSearch extends ActsDoc
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_doc', 'type_act', 'control', 'user_id', 'beg_sald', 'end_sald'], 'integer'],
            [['begdate', 'enddate', 'num_act', 'contr_doc'], 'safe'],
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
        if (Yii::$app->user->can('operator')) {
            $query = ActsDoc::find();
        } else {
            $query = ActsDoc::find()->where(['user_id' => Yii::$app->user->id]);
        }


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['user_id' => SORT_ASC, 'begdate' => SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id_doc' => $this->id_doc,
            'type_act' => $this->type_act,
            'begdate' => $this->begdate,
            'enddate' => $this->enddate,
            'control' => $this->control,
            'user_id' => $this->user_id,
            'beg_sald' => $this->beg_sald,
            'end_sald' => $this->end_sald,
        ]);

        $query->andFilterWhere(['like', 'num_act', $this->num_act])
            ->andFilterWhere(['like', 'contr_doc', $this->contr_doc]);

        return $dataProvider;
    }
}
