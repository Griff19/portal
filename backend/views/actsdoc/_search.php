<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\ActsdocSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="acts-doc-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id_doc') ?>

    <?= $form->field($model, 'type_act') ?>

    <?= $form->field($model, 'begdate') ?>

    <?= $form->field($model, 'enddate') ?>

    <?= $form->field($model, 'num_act') ?>

    <?php // echo $form->field($model, 'contr_doc') ?>

    <?php // echo $form->field($model, 'control') ?>

    <?php // echo $form->field($model, 'user_id') ?>

    <?php // echo $form->field($model, 'beg_sald') ?>

    <?php // echo $form->field($model, 'end_sald') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
