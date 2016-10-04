<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Scans */

$this->title = 'Редактировать документ: ' . ' ' . $model->scan_id;
$this->params['breadcrumbs'][] = ['label' => 'Документы', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->scan_id, 'url' => ['view', 'id' => $model->scan_id]];
$this->params['breadcrumbs'][] = 'Редактировать';
?>
<div class="scans-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
