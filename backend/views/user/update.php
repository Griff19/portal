<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\User */

$this->title = 'Редактировать пользователя: ' . ' ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->username, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="user-update">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php if(Yii::$app->session->hasFlash('fail')){ ?>
        <div class="alert alert-danger">
            <?= Yii::$app->session->getFlash('fail'); ?>
        </div>
    <?php } ?>
    <?php 
    if(isset($update)){
    echo $this->render('_form', [
        'model' => $model,
        'admin' => $admin, 
        ]); 
    }

    if(isset($changepass)){
    echo $this->render('_formpass', [
        'model' => $model,        
    ]);
    }
    ?>

</div>
