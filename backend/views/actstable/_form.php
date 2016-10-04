<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\ActsTable */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="acts-table-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'acts_id')->textInput() ?>

    <?= $form->field($model, 'act_num')->textInput(['maxlength' => 18]) ?>

    <?= $form->field($model, 'date_doc')->textInput() ?>

    <?= $form->field($model, 'num_doc')->textInput(['maxlength' => 11]) ?>

    <?= $form->field($model, 'name_doc')->textInput(['maxlength' => 30]) ?>

    <?= $form->field($model, 'cod_good')->textInput(['maxlength' => 11]) ?>

    <?= $form->field($model, 'beg_sald')->textInput() ?>

    <?= $form->field($model, 'end_sald')->textInput() ?>

    <?= $form->field($model, 'actstable_comm')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
