<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Images */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="images-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'file')->fileInput()?>
    <?= $form->field($model, 'img_oldname')->textInput(['maxlength' => 255]) ?>
    <?= $form->field($model, 'img_newname')->textInput(['maxlength' => 255]) ?>
    <?= $form->field($model, 'img_owner')->textInput(['maxlength' => 255]) ?>
    <?= $form->field($model, 'img_title')->textarea(['maxlength' => 1024])?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Сохранить' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

