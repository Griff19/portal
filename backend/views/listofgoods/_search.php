<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\ListofgoodsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="listofgoods-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'list_id') ?>

    <?= $form->field($model, 'orders_order_id') ?>

    <?= $form->field($model, 'goods_good_id') ?>

    <?= $form->field($model, 'good_count') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
