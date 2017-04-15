<?php

use backend\models\Goods;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\Goods */

$this->title = $model->good_name;
$this->params['breadcrumbs'][] = ['label' => 'Каталог', 'url' => ['catalog']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="goods-view">
    <div class="row">
        <div class="col-sm-6">
            <h1><?= Html::encode($this->title) ?></h1>
            <p>
                <?php
                if (Yii::$app->user->can('operator')) {
                    echo Html::a('Редактировать', ['update', 'id' => $model->good_id], ['class' => 'btn btn-primary']) . ' ';
                    echo Html::a('Удалить', ['delete', 'id' => $model->good_id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Вы уверенны что хотите удалить товар?',
                            'method' => 'post',
                        ],
                    ]) . ' ';
                    echo Html::a($model->status == Goods::DISABLE ? 'Включить' : 'Исключить',
                        ['goods/change-status', 'id' => $model->good_id],
                        ['class' => $model->status == Goods::DISABLE ? 'btn btn-success' : 'btn btn-danger']);
                }
                ?>
            </p>
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    ['attribute' => 'status', 'visible' => Yii::$app->user->can('operator')],
                    ['attribute' => 'good_id', 'visible' => Yii::$app->user->can('operator')],
                    'good_name',
                    'good_description',
                    'good_info',
                    ['attribute' => 'good_price', 'value' => $model->good_price / 100, 'format' => ['decimal', 2]],
                ],
            ]) ?>
        </div>
        <div class="col-sm-6">
            <?php Url::remember(Url::current()); ?>
            <?= Yii::$app->user->can('operator') ? Html::a('Изменить изображение', ['images/select', 'id_good' => $model->good_id]): ''?>
            <?php if (Yii::$app->user->can('operator')) { ?>
            <span class="text-danger">
                <?= Html::a('Удалить', ['goods/set-img', 'id' => $model->good_id]) ?>
            </span><br/>
            <?php } ?>
            <?= Html::img($model->image ? '/' . $model->image->img_newname : '/imgs/empty.jpg') ?>
        </div>
    </div
</div>
</div>
