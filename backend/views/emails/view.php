<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\Emails */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Почта', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="emails-view">
    <?= Html::a('Удалить', ['emails/delete', 'id' => $model->id], [
        'class' => 'btn btn-danger',
        'data' => [
            'confirm' => 'Удалить?',
            'method' => 'post'
        ]])?>
    <div style="padding-top: 5px">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'receiver_name',
            'receiver_email',
            'subject',
            'content_email',
            [
                'attribute' => 'attach',
                'value' => '/' . $model->attach,
                'format' => 'image'
            ]
        ],
    ]) ?>
    </div>
</div>
