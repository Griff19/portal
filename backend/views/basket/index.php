<?php

use backend\models\Basket;
use yii\helpers\Html;

use yii\grid\GridView;
use yii\grid\Column;
use dosamigos\datepicker\DatePicker;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\BasketSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Предварительный заказ';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="basket-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Открыть каталог', ['goods/index'], ['class' => 'btn btn-success']) ?>

    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        //'tableOptions' => ['style' => 'padding:1px'],
        //'rowOptions' => ['style' => 'background-color:black;'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            //'id',
            //'good_id',
            [
                'attribute' => 'good_id',
                'value' => function ($model){
                    $str = $model->goods->good_name;
                    if (!empty($model->goods->good_description)){
                        $str .= ' ' . $model->goods->good_description;
                    }
                    return $str;
                },
            ],
//            [
//                'attribute' => 'good_1c',
//                'value' => 'goods.good_1c_id',
//            ],
            //'count',
            [
                'attribute' => 'count',
                'value' => function ($model){
                    return  $model->count
                            . '<span style="float:right"> '
                            . Html::a('-', [
                                'basket/deleteone', 
                                'id' => $model->id], 
                                ['class' => 'btn btn-primary', 
                                    'style' => 'padding: 2px 7px;'
                                    ])
                            . Html::a('+', [
                                'basket/addone',
                                'id' => $model->id],
                                ['class' => 'btn btn-primary',
                                    'style' => 'padding: 2px 5px;'
                                    ])
                            . '</span>';
                },
                'format' => 'raw', 
            ],
            // цену получаем прямо из таблицы номенклатуры
            ['class' => Column::className(),
                'header' => 'Цена',
                'content' => function($model){
                    return $model->goods->good_price / 100;
                }
            ],
            ['class' => Column::className(),
                'header' => 'Сумма',
                'content' => function($model){
                    $c = $model->count;
                    $p = $model->goods->good_price;
                    return ($c * $p)/100;
                }
            ],
            ['class' => 'yii\grid\ActionColumn',
                'template' => '{delete}'
            ],
                        
        ],
    ]); ?>
    <div style="float: left">
       <?= Html::a('Очистить заказ', ['basket/deleteall'],['class' => 'btn btn-danger']) ?>
    </div>
    <div style="float: right">
       <?php
       //$currDate = date('Y-m-d');
       $s = Basket::getTotals('summ')/100;
       echo '<strong>Всего товаров: ' . Basket::getTotals('count') . '. На сумму: ' . $s . ' </strong>';
       echo Html::a('Оформить заказ', ['orders/create', 'amount' => $s],['class' => 'btn btn-success', 'name' => 'submit']);
       ?>
    </div>

</div>
<?php
//
//$this->registerJs(
//    '$("[name=date_order]").change(
//        function(){
//            $("[name=submit]").attr("href", function(){return "/orders/create.html?amount='. $s .'&date=" + $("[name=date_order]").val() +"";});
//            }
//        )'
//)
?>
