<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\TypicalOrder */

$this->title = 'Update Typical Order: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Typical Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="typical-order-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
