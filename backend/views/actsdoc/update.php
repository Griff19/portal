<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\ActsDoc */

$this->title = 'Update Acts Doc: ' . ' ' . $model->id_doc;
$this->params['breadcrumbs'][] = ['label' => 'Acts Docs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id_doc, 'url' => ['view', 'id' => $model->id_doc]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="acts-doc-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
