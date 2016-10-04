<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ScansSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Документы';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="scans-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Создать документ', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'scan_id',
            'scan_name',
            [
                'attribute' => 'user_id',
                'value' => 'usersUser.username', 
            ],
            [
                'attribute' => 'customer_id',
                'value' => 'customersCustomer.customer_name'
            ],
            ['class' => 'yii\grid\Column',
                'header' => 'Изображение',
                'content' => function ($model){
                    $path = 'scans_up/mini/'.$model->path;
                    return '<a href="index.php?r=scans/download&id='.$model->scan_id.'">'.Html::img($path,['width'=>150]).'</a>';
                },            
            ],
            //'path:image',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
