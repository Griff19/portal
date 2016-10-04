<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Scans;

/**
 * ScansSearch represents the model behind the search form about `backend\models\Scans`.
 */
class ScansSearch extends Scans
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['scan_id'], 'integer'],
            [['scan_name',],'string'],
            [['path', 'user_id', 'customer_id'], 'safe'],
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
        $query = Scans::find()->where(['scans.user_id' => Yii::$app->user->id]);;

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
        $query->joinWith('customersCustomer');
        
        $query->andFilterWhere(['scan_id' => $this->scan_id]);
        $query->andFilterWhere(['like', 'scan_name', $this->scan_name]);
        $query->andFilterWhere(['like', 'path', $this->path]);
        $query->andFilterWhere(['like', 'user.username', $this->user_id]);
        $query->andFilterWhere(['like', 'customers.customer_name', $this->customer_id]);

        return $dataProvider;
    }
}
