<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Images;


/**
 * ImagesSearch represents the model behind the search form about `backend\models\Images`.
 */
class ImagesSearch extends Images
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['img_oldname', 'img_newname', 'img_owner'], 'safe'],
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
     * Поиск и фильтрация моделей. Если вызван из комментария к акту сверки то показываем только его картинки.
     * @param $params
     * @param int|string $owner
     * @return ActiveDataProvider
     */
    public function search($params, $owner = '')
    {
        if(empty($owner)){
            $query = Images::find();
        } else {
            $query = Images::find()->where(['img_owner' => $owner]);
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

        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['like', 'img_oldname', $this->img_oldname])
            ->andFilterWhere(['like', 'img_newname', $this->img_newname])
            ->andFilterWhere(['like', 'img_owner', $this->img_owner]);

        return $dataProvider;
    }
}
