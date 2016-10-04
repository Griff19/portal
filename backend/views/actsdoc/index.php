<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ActsdocSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Акты сверки';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="acts-doc-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php
    if (Yii::$app->user->can('operator')){
        echo '<p>';
        echo Html::a('Загрузить акты', ['uploadform'], ['class' => 'btn btn-success']) . ' ';
        echo Html::a('Скачать с FTP', ['dwnftp'], ['class' => 'btn btn-success']) . ' ';
        echo Html::a('Выгрузить на FTP', ['dbtofile'], ['class' => 'btn btn-success']);
        echo '</p>';
    }?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'rowOptions' => function ($model){
            $str = [];
            if ($model->control == 1){
                $str = ['class' => 'danger'];
            } elseif ($model->control == 2){
                $str = ['class' => 'success'];
            }
            return $str + [
            'onclick' => 'window.location.href="'.Url::toRoute(['actsdoc/view', 'id' => $model->id_doc]).'"; return false',
            'style' => 'cursor:pointer'
            ];
        },

        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id_doc',
            //'type_act',
            [
                'attribute' => 'type_act',
                'value' => function($model){
                    return $model->type_act == 0 ? 'Взаиморасчеты' : 'Сверка тары';
                }
            ],

            'num_act',
            'begdate',
            'enddate',
            'good',

            [
                'attribute' => 'user_id',
                'value' => 'user.username',
            ]
            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
