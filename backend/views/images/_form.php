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
    <?php //$form->field($model, 'img_oldname')->textInput(['maxlength' => 255]) ?>
    <?php //$form->field($model, 'img_newname')->textInput(['maxlength' => 255]) ?>

    <?= $form->field($model, 'img_title')->textarea(['maxlength' => 1024]) ?>
    <p>Для назначения одной картинки группе товаров по идентификатору 1с, введите необходимый идентификатор в поле
        <b><?= $model->getAttributeLabel('img_owner')?></b>:</p>
    <?= $form->field($model, 'img_owner')->textInput(['maxlength' => 255]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Сохранить' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

