<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\TypePrice */

$this->title = 'Update Type Price: ' . ' ' . $model->type_price_id;
$this->params['breadcrumbs'][] = ['label' => 'Type Prices', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->type_price_id, 'url' => ['view', 'id' => $model->type_price_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="type-price-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
