<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\GoodsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="goods-search">

    <?php $form = ActiveForm::begin([
        'action' => ['catalog'],
        'method' => 'get',
    ]); ?>

    <?php //$form->field($model, 'good_id') ?>

    <?= $form->field($model, 'good_name') ?>

    <?= $form->field($model, 'good_description') ?>

    <?php //$form->field($model, 'good_price') ?>

    <?= Html::submitButton('Найти', ['class' => 'btn btn-primary']) ?>
    <?= Html::resetButton('Сброс', ['class' => 'btn btn-default']) ?>

    <?php ActiveForm::end(); ?>

</div>
