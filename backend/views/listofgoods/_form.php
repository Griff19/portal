<?php

use backend\models\Goods;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Listofgoods */
/* @var $orders_order_id backend\models\Listofgoods */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="listofgoods-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php
        if($orders_order_id != 0){
            echo '<p> Номер текущего заказа ' . $orders_order_id . '</p>';
        } else {
            echo $form->field($model, 'orders_order_id')->textInput(['value'=>$orders_order_id]);
        }
    ?>

    <?= $form->field($model, 'goods_good_1c_id')->dropDownList(
            ArrayHelper::map(Goods::find()->where(['typeprices_id' => $tp])->all(),'good_id','good_name'),
            ['prompt'=>'Выберите товар...']
            ); ?>

    <?= $form->field($model, 'good_count')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Готово' : 'Редактировать', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
