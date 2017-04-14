<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\Images */

$this->title = $model->img_oldname;
$this->params['breadcrumbs'][] = ['label' => 'Изображения', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="images-view">
    <div class="row">
        <div class="col-md-6">
            <h1><?= Html::encode($model->img_oldname) ?></h1>
            <?= Html::img('/' . $model->img_newname, ['style' => 'width:300px']) ?>

        </div>
        <div class="col-md-6">
            <p>
                <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'Хотите удалить?',
                        'method' => 'post',
                    ],
                ]) ?>
                <?= Html::a('Новое изображение', ['create'], ['class' => 'btn btn-success'])?>
            </p>
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'img_oldname',
                    'img_newname',
                    'img_owner',
                    'img_title'
                ],
            ]) ?>
        </div>
    </div>
</div>
