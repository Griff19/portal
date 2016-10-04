<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin();
        echo $form->field($model, 'username')->textInput(['maxlength' => 255]);
        echo $form->field($model, 'email')->textInput(['maxlength' => 255]);

        if ($admin){
            echo $form->field($model, '_1c_id')->textInput(['maxlength' => 36]);
            echo $form->field($model, 'fullname')->textInput(['maxlength' => 255]);
            echo $form->field($model, 'newpass')->passwordInput();
            echo $form->field($model, 'confnewpass')->passwordInput();
        }
    ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Редактировать', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
