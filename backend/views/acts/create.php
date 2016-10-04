<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Acts */

$this->title = 'Загрузка архива актов сверок';
$this->params['breadcrumbs'][] = ['label' => 'Акты', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="acts-create">
    <?php if(Yii::$app->session->hasFlash('fail')){
        echo '<div class="alert alert-danger">';
        echo Yii::$app->session->getFlash('fail');
        echo '</div>';
    } ?>
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
