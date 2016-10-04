<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Acts */

$this->title = 'Update Acts: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Acts', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="acts-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
