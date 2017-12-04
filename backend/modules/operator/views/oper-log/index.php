<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OperLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Oper Logs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="oper-log-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Oper Log', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'created_at:datetime',
            'user_id',
            'action',
            'desc',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
