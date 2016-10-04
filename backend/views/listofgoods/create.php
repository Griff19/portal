<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Listofgoods */

$this->title = 'Добавить товар к заказу №'.$orders_order_id;
$this->params['breadcrumbs'][] = ['label' => 'Заказ №'.$orders_order_id, 'url' => ['orders/view','id'=>$orders_order_id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="listofgoods-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'orders_order_id' => $orders_order_id,
        'tp' => $tp,
    ]) ?>

</div>
