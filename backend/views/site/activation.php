<?php

use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 13.07.2016
 * Time: 9:42
 */
$this->title = 'Активация аккаунта';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-activation" align="center">
    <h1><?= 'Добро пожаловать!' ?></h1>
    <?php if ($deny) {?>
        <h2> На сайте ведутся технические работы! Приносим извинения за неудобства. </h2>
    <?php } ?>
    <p>Для активации аккаунта заполните следующие поля.<br>
        Вводите данные, указанные в договоре.</p>

    <div class="row">
        <div style="width: 250px">
            <?php $form = ActiveForm::begin([
                'id' => $model->formName(),
                //'enableAjaxValidation' => true,
                //'validationUrl' => Url::toRoute('site/validation')
            ]); ?>

            <?php
                echo $form->field($model, 'customer_email')->textInput(['maxlength' => 200]);
                echo $form->field($model, 'inn')->textInput(['maxlength' => 12]);
                echo 'Введите текст с картинки в поле ниже:';
                echo $form->field($model, 'capch')->widget('\yii\captcha\Captcha');
            ?>

            <div class="form-group">
                <?php
                echo Html::submitButton('Активировать', ['class' => 'btn btn-success']);
                ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>

    </div>

</div>
