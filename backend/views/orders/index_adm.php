<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use backend\models\ListofgoodsSearch;
use backend\models\Orders;


/* @var $this yii\web\View */
/* @var $searchModel backend\models\OrdersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Заказы';
$this->params['breadcrumbs'][] = $this->title;
?>

    
<div class="orders-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php 
        //echo $this->render('_search', ['model' => $searchModel]); 
        echo '<p>Администратор может просматривать все заказы.</p>';
    ?>

    <p>
        <?= Html::a('Создать Заказ', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Выгрузить', ['dbtofile'], ['class' => 'btn btn-success']) ?>
        <?php
        if($getFile){
            echo '<a href="index.php?r=orders/download"> Скачать файл </a>';
        }
        ?>
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
            'order_timestamp',
            [
                'attribute' => 'user_id',
                'value' => 'usersUser.fullname'
            ],
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
    <?php 
        //тут я тренируюсь выгружать данные в файл
        echo '<b> Выгружаемые Данные: </b><br>';
        $order = new Orders;
        $orders = $order->find()->where(['status' => 'Размещен'])->all();
            
        foreach($orders as $dt){
            //print_r($dt);
            $sm = new ListofgoodsSearch();
            $dp = $sm->search(Yii::$app->request->queryParams,$dt['order_id']);
                
            foreach ($dp->models as $mod){
                echo ' '.$dt['order_id'];
                echo ' '.$dt['customersCustomer']['customer_name'];
                echo ' '.$mod['goodsGoodId']['good_name'];
                $good_price = $mod['goodsGoodId']['good_price'] / 100;
                echo ' ' . $good_price;
                echo '<br>';
            }
        };
    ?>

</div>
