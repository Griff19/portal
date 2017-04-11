<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\grid\Column;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ImagesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Изображения';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="images-index">

    <h1><?= Html::encode($this->title) ?></h1>


    <p>
        <?php
        if (Yii::$app->user->can('operator'))
            echo Html::a('Добавить изображение', ['create'], ['class' => 'btn btn-success']);

        ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            ['class' => Column::className(),
                'header' => 'Изображение',
                'content' => function ($model) {
                    return Html::img('/' . $model->img_newname, ['style' => 'width:100px']);
                }
            ],
            'img_oldname',
            'img_newname',
            'img_owner',
            'img_title',

            ['class' => 'yii\grid\Column',
                'content' => function ($model) use ($id_good) {
                    return Html::a('<span class="glyphicon glyphicon-ok"></span>',
                        ['goods/set-img', 'id' => $id_good, 'id_img' => $model->id],
                        ['title' => 'Выбрать это изображение...']
                    );
                }
            ],
        ],
    ]); ?>

</div>
