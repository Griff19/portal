<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\ActsDoc */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="acts-doc-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
    <?php 
    if ($upload){
        echo $form->field($model, 'file')->fileInput();
    } else {
        echo $form->field($model, 'type_act')->textInput();
        echo $form->field($model, 'begdate')->textInput();
        echo $form->field($model, 'enddate')->textInput();
        echo $form->field($model, 'num_act')->textInput(['maxlength' => 11]);
        echo $form->field($model, 'contr_doc')->textInput(['maxlength' => 9]);
        echo $form->field($model, 'control')->textInput();
        echo $form->field($model, 'user_id')->textInput();
        echo $form->field($model, 'beg_sald')->textInput();
        echo $form->field($model, 'end_sald')->textInput();
    }?>
    <div class="form-group">
        <?php
        if($upload){
            echo Html::submitButton('Загрузить', ['class' => 'btn btn-success']);
        } else {
            echo Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']);
        }
        ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
