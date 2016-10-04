<?php

use backend\models\Customers;
use backend\models\User;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use dosamigos\datepicker\DatePicker;

/* @var $this yii\web\View */
/* @var $model backend\models\Orders */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="orders-form">

    <?php
    $form = ActiveForm::begin();
    //тут нужно сделать проще!!!
    if ((Yii::$app->user->can('subuser')) && !(Yii::$app->user->can('user'))) {
        $usr = User::findOne(['id' => Yii::$app->user->id]);
        echo $form->field($model, 'customers_customer_id')->dropDownList(
            ArrayHelper::map(Customers::find()->where(['customer_name' => $usr->fullname])->all(), 'customer_id', 'customer_name'),
            ['prompt' => 'Выберите контрагента...']
        );
    } else {
        echo $form->field($model, 'customers_customer_id')->dropDownList(
            ArrayHelper::map(Customers::find()->where(['user_id' => Yii::$app->user->id])->all(), 'customer_id', 'customer_name'),
            ['prompt' => 'Выберите контрагента...']
        );
    }
    ?>
    <?= $form->field($model, 'order_date')->widget(
        DatePicker::className(), [
        'inline' => false,
        'clientOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd',
        ]
    ])?>
    <?php
    //$model->order_date = date('Y-m-d');
    echo '<br>';
    echo Html::activeLabel($model, 'order_amount');
    if (isset($amount)){
        echo ' ' . $amount;
    }else{
        echo ' ' . $model->order_amount / 100;
    }
    echo '<br>';
    ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Редактировать', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
