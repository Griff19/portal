<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model backend\models\Orders */

$this->title = 'Заказ №' . $model->order_id;
$this->params['breadcrumbs'][] = ['label' => 'Заказы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="orders-view">

    <h1>
        <?= Html::encode($this->title); ?>
        <?php
        if ($model->status == 'Черновик') {
            echo Html::a('Удалить заказ', ['delete', 'id' => $model->order_id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Действительно хотите удалить заказ?',
                    'method' => 'post',
                ],
            ]);
        }
        ?>

    </h1>
    <?php 
    $model->order_amount = $model->order_amount / 100;
    ?>
        <?= DetailView::widget([
        'model' => $model,
        'attributes' => [

            'order_id',
            //'order_timestamp',
            [
                'attribute' => 'order_timestamp',
                'value' => substr($model->order_timestamp,0,19),
            ],
            [
                'attribute' => 'order_date',
                'value' => $model->order_date
            ],
            'customersCustomer.customer_name',
            'order_amount',         
            [
                'attribute' => 'user_id',
                'value' => $model->usersUser->username,
            ],
            [
                'attribute' => 'status',
                'class' => $model->status == 'Размещен' ? 'success' : '',
                'value' => $model->status,
            ],
        ],
    ])
    ?>
    <p>
        
        <?php
        if ($model->status == 'Черновик') {
            echo Html::a('Редактировать', ['update', 'id' => $model->order_id], ['class' => 'btn btn-primary']);
            echo ' ';
            echo Html::a('Разместить', ['place', 'id' => $model->order_id], ['class' => 'btn btn-primary']);
        } elseif ($model->status == 'Размещен'){
            if (Yii::$app->user->can('operator')){
                echo Html::a('Заблокировать', ['agree', 'id' => $model->order_id], ['class' => 'btn btn-primary']);
            }
            echo ' ';
            echo Html::a('Снять', ['unplace', 'id' => $model->order_id], ['class' => 'btn btn-primary']);
            
        } else {
            if (Yii::$app->user->can('operator')){
                echo Html::a('Разблокировать', ['disagree', 'id' => $model->order_id], ['class' => 'btn btn-primary']);
                echo ' '; 
            }
            echo Html::a('Повторить', ['copy', 'id' => $model->order_id], [
                'class' => 'btn btn-primary',
                'data' => [
                    'confirm' => 'Скопировать текущий заказ?',
                    ],
                ]);
        }
        ?>
    </p>
    <div class="listofgoods-index">       
        <?php
        
        $col1 = [
            ['class' => 'yii\grid\SerialColumn'],
            //'list_id',
            'orders_order_id',
            [
                'attribute' => 'goods_good_1c_id',
                'value' => function ($model){
                    $str = $model->goodsGoodId->good_name;
                    if (!empty($model->goodsGoodId->good_description)){
                        $str .= ' ' . $model->goodsGoodId->good_description;
                    }
                    return $str;
                }
            ],
            'good_count',
            [
                'attribute' => 'goodsGoodId.good_price',
                'value' => function ($model) {
                    return $model->goodsGoodId->good_price / 100;
                }
            ],
            
            ['class' => 'yii\grid\Column',
                'header' => 'Сумма',
                'content' => function ($model) {
                    $p = $model->goodsGoodId->good_price / 100;
                    $c = $model->good_count;
                    return ($p * $c);
                },
            ],
            ['class' => 'yii\grid\Column',
                'header' => '',
                'content' => function ($model) {
                    //var_dump($model);
                    return Html::a('Удалить', ['listofgoods/delete', 'id' => $model->list_id], ['data-method' => 'post']);
                },
            ],
        ];
        //var_dump($model);
        //если заказ еще не размещен - его можно редактировать
        //но когда его уже разместили то разрешен только просмотр
        if ($model->status == 'Черновик') {
        } else {
            unset($col1[6]);
        }
        ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => $col1,
            ])
        ?>
        <p>
            <?php
            if ($model->status == 'Черновик') {
                //echo Html::a('Добавить товар', ['listofgoods/create', 'order_id' => $model->order_id, 'amount' => $model->order_amount], ['class' => 'btn btn-success']);
                echo Html::a('Добавить товары', ['goods/index', 'order_id' => $model->order_id],['class' => 'btn btn-success']);
                
            }
            ?>
        </p>    
    </div>   
</div>
