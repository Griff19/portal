<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 28.05.2016
 * Time: 22:24
 */

namespace backend\models;

use Yii;
use yii\data\ActiveDataProvider;

class EmailsSearch extends Emails
{
    public function search($params){

        $query = Emails::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $dataProvider;
    }
}