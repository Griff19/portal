<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\Column;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\GoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Каталог товаров';
$this->params['breadcrumbs'][] = $this->title;

$i = 0;

?>
<div class="goods-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    
    ?>

    <p>
        <?= Html::a('Добавить товар', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Загрузить файл', ['uploadform'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php Pjax::begin()?>
    <?php 
        if ($order_id > 0){
            $straction = '/alt_bur/backend/web/index.php?r=listofgoods/insert&order_id='.$order_id;
        }else{
            $straction = '/alt_bur/backend/web/index.php?r=basket/insert';
        }
        echo '<form id = "goods" action = "' . $straction . '" method="post" enctype="multipart/form-data">';
    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [

            'good_id',
            'good_name',

            [
                'attribute' => 'good_price',
                'value' => function ($model){
                    return $model->good_price / 100;
                }
            ],
            'typeprices_id',
            ['class' => Column::className(),
                'header' => '',
                'content' => function ($model) use ($dataProvider,&$i) 
                {
                    //return Html::a('Добавить в корзину!',['basket/insert', 'good_id' => $model->good_id, 'count' => 1, 'price' => $model->good_price, 'str' => $dataProvider->keys[$i++]]);
                    $id = $dataProvider->keys[$i++];
                    return '<input type="number" id = "'.$id.'" class="form-control" name="count_'.$id.'" style="float:left; width:60px" value="0" max="100" min="0">'
                            . '<input type="hidden" id = "'.$id.'" name = "good_'.$id.'" value = "'.$id.'" >'
                            . '<input type="hidden" id = "'.$id.'" name = "price_'.$id.'" value = "'.$model->good_price.'" >';
                
                },
                'contentOptions' => ['style' => 'width:115px'],        
            ],

            //['class' => 'yii\grid\ActionColumn'],
                    
        ],
    ]); 
    ?>
    <?php 
        echo '<input type="submit" class = "btn btn-success" value="Заказать">'; 
        echo '</form>'
    ?>   
    <?php Pjax::end()?>

</div>
