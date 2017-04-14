<?php

use backend\models\Goods;
use yii\helpers\Html;
use yii\grid\GridView;
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
        <?= Html::a('Скачать с FTP', ['download'], ['class' => 'btn btn-success']) ?>
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
        'tableOptions' => ['class' => 'table-bordered'],
        'rowOptions' => function ($model) {
            if ($model->status == Goods::DISABLE)
                return ['style' => 'opacity: 0.5'];
        },
        'columns' => [

            'good_id',
            'good_1c_id',
            ['attribute' => 'good_name',
                'value' => function ($model) {
                    return Html::a($model->good_name, ['view', 'id' => $model->good_id]);
                },
                'format' => 'raw',
            ],
            'good_description',
            ['attribute' => 'good_price',
                'value' => function ($model){
                    return $model->good_price / 100;
                }
            ],
            'typeprices_id',
            'tPname.type_price_name',
            'status',
                    
        ],
    ]); 
    ?>
    <?php 
        echo '<input type="submit" class = "btn btn-success" value="Заказать">'; 
        echo '</form>'
    ?>   
    <?php Pjax::end()?>

</div>
