<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Responsible */

$this->title = 'Update Responsible: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Responsibles', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="responsible-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
