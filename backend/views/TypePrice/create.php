<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\TypePrice */

$this->title = 'Create Type Price';
$this->params['breadcrumbs'][] = ['label' => 'Type Prices', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="type-price-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
