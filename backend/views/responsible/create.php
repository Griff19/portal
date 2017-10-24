<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Responsible */

$this->title = 'Добавиление ответственного лица';
$this->params['breadcrumbs'][] = ['label' => 'Контрагенты', 'url' => ['/customers']];
$this->params['breadcrumbs'][] = ['label' => $model->customer->customer_name, 'url' => ['customers/view', 'id' => $model->customer_id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="responsible-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
