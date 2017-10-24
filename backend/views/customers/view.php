<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;

/**
 * @var $this yii\web\View
 * @var $model backend\models\Customers
 * @var $responsibleSearch \backend\models\ResponsibleSearch
 * @var $responsibleData \backend\models\Responsible
 */

$this->title = $model->customer_name;
$this->params['breadcrumbs'][] = ['label' => 'Контрагенты', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customers-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php if (Yii::$app->user->can('operator')) {?>
    <p>
        <?= Html::a('Редактировать', ['update', 'id' => $model->customer_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->customer_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Хотите удалить контрагента?',
                'method' => 'post',
            ],
        ]) ?>
    </p>
    <?php } ?>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'customer_id',
            'user_id',
            'customer_name',
            'inn',
            'customer_email:email',
        ]
    ]) ?>
    <h3>Телефоны:</h3>
    <?php
    echo GridView::widget([
            'dataProvider' => $dataPhones,
            'filterModel' => $searchPhone,
            'emptyText' => 'Еще нет телефонов...'
    ]);
    echo Html::a("Добавить телефон", ['phone/create', 'customer_id' => $model->customer_id], ['class' => 'btn btn-primary']);
    ?>
    <h3>Ответственные:</h3>
    <?php
    echo GridView::widget([
            'dataProvider' => $responsibleData,
            'filterModel' => $responsibleSearch,
    ]);
    echo Html::a("Добавить ответственное лицо", ['responsible/create', 'customer_id' => $model->customer_id], ['class' => 'btn btn-primary']);
    /** todo: Вывести в описание контрагента всех ответственных лиц, отработать процесс добавления новых */
    ?>

</div>
