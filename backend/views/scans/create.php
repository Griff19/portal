<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Scans */

$this->title = 'Загрузка документа';
$this->params['breadcrumbs'][] = ['label' => 'Документы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="scans-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
