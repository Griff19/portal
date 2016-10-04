<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\ActsTable */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Acts Tables', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="acts-table-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'acts_id',
            'act_num',
            'date_doc',
            'num_doc',
            'name_doc',
            'cod_good',
            'beg_sald',
            'end_sald',
            'actstable_comm:ntext',
        ],
    ]) ?>

</div>
