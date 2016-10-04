<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Acts;

/**
 * ActsSearch represents the model behind the search form about `backend\models\Acts`.
 */
class ActsSearch extends Acts
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'customers_customer_1c_id'], 'integer'],
            [['users_user_1c_id','typedoc'], 'string'],
            [['num', 'begdate', 'enddate', 'begenddate'], 'safe'],
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
    public function search($params, $_1cid)
    {
        if(Yii::$app->user->can('admin')){
            $query = Acts::find();
        } else {
            $query = Acts::find()->where(['users_user_1c_id' => $_1cid]);
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
        $query->andFilterWhere([
            'id' => $this->id,
            'begdate' => $this->begdate,
            'enddate' => $this->enddate,
            //'users_user_1c_id' => $this->users_user_1c_id,
            'customers_customer_1c_id' => $this->customers_customer_1c_id,
            
        ]);

        $query->andFilterWhere(['like', 'num', $this->num])
            ->andFilterWhere(['like', 'begenddate', $this->begenddate])
            ->andFilterWhere(['like', 'user.fullname', $this->users_user_1c_id])    
            ->andFilterWhere(['like', 'typedoc', $this->typedoc]);

        return $dataProvider;
    }
}
