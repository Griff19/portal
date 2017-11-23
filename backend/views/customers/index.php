<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CustomersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Магазины/Отделы';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customers-index">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <?php 
    if(Yii::$app->user->can('admin')){
        echo '<p>Вы видите все магазины/отделы.</p>';
    } else {   
        echo '<p>Закрепленные за вами магазины/отделы.</p>';
    }
        // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php if (Yii::$app->user->can('operator')){ ?>
    <p>
        <?= Html::a('Добавить контрагента', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Загрузить', ['uploadform'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Загрузить c FTP', ['dwnftp'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php } ?>

    <?php
    $cols = [
        ['class' => 'yii\grid\SerialColumn'],
        ['attribute' => 'customer_id', 'visible' => Yii::$app->user->can('admin')],
        [
            'attribute' => 'user_id',
            'value' => 'usersUser.fullname',
        ],

        'customer_name',
        'customer_email:email',
    ];

    if (Yii::$app->user->can('operatorSQL')) $cols[] = ['class' => 'yii\grid\ActionColumn'];

    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $cols
        ]);
    ?>

</div>
