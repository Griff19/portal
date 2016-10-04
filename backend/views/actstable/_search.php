<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\ActstableSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="acts-table-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'acts_id') ?>

    <?= $form->field($model, 'act_num') ?>

    <?= $form->field($model, 'date_doc') ?>

    <?= $form->field($model, 'num_doc') ?>

    <?php // echo $form->field($model, 'name_doc') ?>

    <?php // echo $form->field($model, 'cod_good') ?>

    <?php // echo $form->field($model, 'beg_sald') ?>

    <?php // echo $form->field($model, 'end_sald') ?>

    <?php // echo $form->field($model, 'actstable_comm') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
