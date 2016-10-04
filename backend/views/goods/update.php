<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Goods */

$this->title = 'Редактировать товар: ' . ' ' . $model->good_name;
$this->params['breadcrumbs'][] = ['label' => 'Товары', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->good_name, 'url' => ['view', 'id' => $model->good_id]];
$this->params['breadcrumbs'][] = 'Редактировать';
?>
<div class="goods-update">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php 
        if ($upload){
            echo '<p>Изображение для товара:</p>';
        }
    ?>

    <?= $this->render('_form', [
        'model' => $model,
        'upload' => $upload,
    ]) ?>

</div>
