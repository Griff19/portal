<?php

use backend\models\Goods;
use backend\models\Images;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\Column;
//use yii\grid\SerialColumn;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\GoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Каталог товаров';
$this->params['breadcrumbs'][] = $this->title;

$i = 0; //индекс помогает получать из провайдера id

?>
<div class="goods-index" style="padding-bottom: inherit">
    <p>Для создания заявки выберите количество необходимого товара в колонке "кол-во" таблицы. Затем в шапке страницы нажмите поле "Заказ..."</p>
    <h4>Заявки принимаются до <span style="color: red">13:00</span>, просроченные заявки обработаются на следующий день</h4>
    <h1><?= Html::encode($this->title) ?></h1>
    <?php if (Yii::$app->user->can('operator')){ ?>
        <p>
        <?= Html::a('Добавить товар', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Загрузить файл', ['uploadform'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Скачать с FTP', ['download'], ['class' => 'btn btn-success']) ?>
        </p>
    <?php }?>

<?php //
//        if ($order_id > 0){
//            $straction = '/alt_bur/backend/web/index.php?r=listofgoods/insert&order_id='.$order_id;
//        }else{
//            $straction = '/alt_bur/backend/web/index.php?r=basket/insert';
//        }
//        echo '<form id = "goods" action = "' . $straction . '" method="post" enctype="multipart/form-data">';
//    ?>
    <?php
        $col = [
            [
                'attribute' => 'good_1c_id',
                'contentOptions' => ['style' => ' width:60px;']
            ],
            //ссылка на добавление изображения
            ['class' => Column::className(),
                'header' => '(i)',
                'content' => function ($model){
                    //return Html::a('+',['setimg', 'id' => $model->good_id]);
                    return Html::a('+', ['images/create', 'owner' => Goods::tableName() . $model->hash_id]);
                }
            ],
            //наименование товара, всплывающее изображение
            [
                'attribute' => 'good_name',
                'format' => 'raw',
                'value' => function($model){
                    $img_link = '';
                    $img = Images::getImage(Goods::tableName() . $model->hash_id);
                    $title = Images::getTitle(Goods::tableName() . $model->hash_id);
                    if ($img || $title)
                        return '<a id = "'.$model->good_id.'" name = "info" data-img = "'. $img .'" data-title = "'. $title .'" data-placement="right">'.$model->good_name.'</a>';
                    else
                        return $model->good_name;
                },
                'contentOptions' => ['style' => 'position:relative']
            ],
            //дополнение
            [
                'attribute' => 'good_description',
                'contentOptions' => ['style' => ' width:300px;']
            ],
            //цена товара
            [
                'attribute' => 'good_price',
                'value' => function ($model){
                    return $model->good_price / 100;
                },
                'contentOptions' => ['style' => ' width:60px;']
            ],
            //поле для добавления товара в корзину, обрабатывается через jQuery
            //web\js\main.js
            ['class' => Column::className(),
                'header' => 'кол-во',
                'content' => function ($model) use ($dataProvider, &$i)
                {
                    $id = $dataProvider->keys[$i++];
                    return '<input type="number" id = "'.$model->hash_id
                    .'" name="count_'.$id
                    .'" style="float:left; width:60px; height:20px;'
                    .'" value="'.$model->basketCount
                    .'" max="100" min="0" oninput = changeNumber()>';
                    //. '<input type="hidden" id = "'.$id.'" name = "good_'.$id.'" value = "'.$id.'" >'
                    //. '<input type="hidden" id = "'.$id.'" name = "price_'.$id.'" value = "'.$model->good_price.'" >';
                },
                'contentOptions' => ['style' => 'padding:1px; width:84px;']
            ],
        ];
        if (!Yii::$app->user->can('operator')) {unset($col[0]); unset($col[1]);}
    ?>
    <?php Pjax::begin();?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => ['class' => 'table-bordered'],
        'columns' => $col,
    ]); 
    ?>
    <?php
        $this->registerJs('showInfo()');
        $this->registerJs('changeNumber()');
    ?>
    <?php Pjax::end(); ?>
    <?php
        echo '<br>';
        //echo '<input type="submit" class = "btn btn-success" value="Заказать!">';
        echo Html::a('Заказать!', ['listofgoods/insertall', 'order_id'=> $order_id], ['class'=>'btn btn-success', 'style' => '']);

    ?>

    <div id="regionPopContent" style="display:none;">
        <div class="tooltip-arrow"></div>
        <div class="container-fluid">подсказка</div>
    </div>
</div>