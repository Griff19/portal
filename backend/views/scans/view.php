<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\Scans */

$this->title = $model->scan_name;
$this->params['breadcrumbs'][] = ['label' => 'Документы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="scans-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Обновить', ['update', 'id' => $model->scan_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->scan_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Хотите удалить документ?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('Скачать', ['download', 'id' => $model->scan_id], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            //'scan_id',
            [
                'attribute' => 'user_id',
                'value' => $model->usersUser->username,
            ],
            //'user_id',
            [
                'attribute' => 'customer_id',
                'value' => $model->customersCustomer->customer_name,
            ],
            //'customer_id',
            [
                'label' => 'Изображение',
                'format' => 'html',
                'value' => '<a href="index.php?r=scans/download&id='.$model->scan_id.'">'. Html::img('scans_up/mini/'.$model->path).'</a>',
                
            ],
            //'path:image',
        ],
    ]) ?>

</div>
