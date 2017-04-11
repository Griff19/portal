<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Goods */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="goods-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?php 
    if($upload){
        echo $form->field($model, 'file')->fileInput();
    } else {
        echo $form->field($model, 'good_1c_id')->textInput();
        echo $form->field($model, 'good_name')->textInput(['maxlength' => 200]); 
        echo $form->field($model, 'good_info')->textInput(['maxlength' => 100]);
        echo $form->field($model, 'good_price_real')->textInput();
        echo $form->field($model, 'discount')->checkbox();
    }
    
    ?>

    <div class="form-group">
        <?php
        if ($upload){
            echo Html::submitButton('Загрузить',['class' => 'btn btn-success']);
        } else {
            echo Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']);
        }   
        ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
