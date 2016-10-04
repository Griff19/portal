<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Listofgoods */

$this->title = 'Update Listofgoods: ' . ' ' . $model->list_id;
$this->params['breadcrumbs'][] = ['label' => 'Listofgoods', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->list_id, 'url' => ['view', 'id' => $model->list_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="listofgoods-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
