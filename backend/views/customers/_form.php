<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use common\models\User;
use backend\models\Typeprice;

/* @var $this yii\web\View */
/* @var $model backend\models\Customers */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="customers-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?php //echo $form->field($model, 'user_id')->textInput() ?>

    <?php
    if ($upload){
        echo $form->field($model, 'file')->fileInput();
    } else {
        if (Yii::$app->user->can('admin')){
            echo $form->field($model,'user_id')->dropDownList(
            ArrayHelper::map(User::find()->all(), 'id', 'fullname'),
                    ['prompt' => 'Выберите пользователя']);
        }
        echo $form->field($model, 'customer_1c_id')->textInput();
        echo $form->field($model, 'customer_name')->textInput(['maxlength' => 200]);
        echo $form->field($model, 'customer_email')->textInput(['maxlength' => 200]);
        echo $form->field($model, 'inn')->textInput(['maxlength' => 12]);
        echo $form->field($model, 'typeprices_id')->dropDownList(
        ArrayHelper::map(Typeprice::find()->all(), 'type_price_id','type_price_name'),
        ['prompt' => 'Выберите тип цен']);
    }
            ?>

    <div class="form-group">
        <?php
        if ($upload){
            echo Html::submitButton('Загрузить',['class' => 'btn btn-success']);
        } else {
            echo Html::submitButton($model->isNewRecord ? 'Создать' : 'Редактировать', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']);
        }   
        ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
