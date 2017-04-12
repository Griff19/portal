<?php


use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Images */

$this->title = 'Новое изображение';
$this->params['breadcrumbs'][] = ['label' => 'Изображения', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="images-create">

    <h1><?= Html::encode($this->title) ?></h1>
    <p>Для успешной загрузки размер изображения не должен превышать 1000x1000px. Расширение - png, jpg, jpeg</p>
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
