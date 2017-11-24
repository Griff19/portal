<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\TypicalOrder */

$this->title = 'Create Typical Order';
$this->params['breadcrumbs'][] = ['label' => 'Typical Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="typical-order-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
