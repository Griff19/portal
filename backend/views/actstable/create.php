<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\ActsTable */

$this->title = 'Create Acts Table';
$this->params['breadcrumbs'][] = ['label' => 'Acts Tables', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="acts-table-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
