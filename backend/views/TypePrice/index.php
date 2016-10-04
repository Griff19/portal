<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\TypePriceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Type Prices';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="type-price-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Type Price', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'type_price_id',
            'type_price_name',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
