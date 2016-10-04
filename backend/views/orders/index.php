<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OrdersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Заказы';
$this->params['breadcrumbs'][] = $this->title;
?>

    
<div class="orders-index">
    <h4>Заявки принимаются до <span style="color: red">13:00</span>, просроченные заявки обработаются на следующий день.</h4>
    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <p>
        <?= Html::a('Создать Заказ', ['goods/index'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'rowOptions' => function ($model){
            if ($model->status == 'Размещен'){
                return [
                    'class' => 'success',
                    'onclick' => 'window.location.href="'.Url::toRoute(['orders/view', 'id' => $model->order_id]).'"; return false',
                    'style' => 'cursor:pointer'
                ];
            } elseif ($model->status == 'Черновик'){
                return [
                    'class' => 'danger',
                    'onclick' => 'window.location.href="'.Url::toRoute(['orders/view', 'id' => $model->order_id]).'"; return false',
                    'style' => 'cursor:pointer'
                ];
            } else {
                return[
                    'onclick' => 'window.location.href="'.Url::toRoute(['orders/view', 'id' => $model->order_id]).'"; return false',
                    'style' => 'cursor:pointer'
                ];
            }
        },
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'order_id',
            //'order_timestamp',
            [
                'attribute' => 'order_timestamp',
                'value' => function ($model){
                    return substr($model->order_timestamp,0,19);
                },
            ],
            'order_date',
            [
                'attribute' => 'customers_customer_id',
                'value' => 'customersCustomer.customer_name',
            ],
            [
                'attribute' => 'order_amount',
                'value' => function ($model){
                    return $model->order_amount / 100;
                }
            ],
            //'user_id',
            'status',
            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>

</div>
