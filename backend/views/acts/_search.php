<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\ActsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="acts-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'num') ?>

    <?= $form->field($model, 'begdate') ?>

    <?= $form->field($model, 'enddate') ?>

    <?= $form->field($model, 'users_user_id') ?>

    <?php // echo $form->field($model, 'customers_customer_id') ?>

    <?php // echo $form->field($model, 'begenddate') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
