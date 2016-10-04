<?php
use backend\models\Customers;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Scans */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="scans-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'scan_name')->textInput() ?>
    
    <?= $form->field($model, 'customer_id')->dropDownList(
        ArrayHelper::map(Customers::find()->where(['user_id' => Yii::$app->user->id])->all(),'customer_id','customer_name'),
        ['value' => $model->customer_id,
        'prompt'=>'Выберите контрагента...']
    ) ?>

    <?php // echo $form->field($model, 'path')->textInput(['maxlength' => 200]) ?>
    <?= $form->field($model, 'file')->fileInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Загрузить' : 'Редактировать', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
