<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Goods */
if ($upload){
    $this->title = 'Загрузить номерклатуру';
} else {
    $this->title = 'Создать товар';
}
$this->params['breadcrumbs'][] = ['label' => 'Товары', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="goods-create">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <?= $this->render('_form', [
        'model' => $model,
        'upload' => $upload,
    ]) ?>

</div>
