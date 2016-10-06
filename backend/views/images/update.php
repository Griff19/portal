<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Images */

$this->title = 'Редактировать изображение: ' . ' ' . $model->img_oldname;
$this->params['breadcrumbs'][] = ['label' => 'Изображения', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->img_oldname, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Редактировать';
?>
<div class="images-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
