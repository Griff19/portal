<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Acts */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="acts-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
    
    <?// echo $form->field($model,'filereestr')->fileInput()?>
    <?// echo Html::a('Загрузить реестр', ['uploadreestr'], ['class' => 'btn btn-success']) ?>
    <?= $form->field($model,'filezip')->fileInput()?>
    

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Загрузить' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
