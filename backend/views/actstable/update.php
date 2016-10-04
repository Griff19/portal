<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\ActsTable */

$this->title = 'Комментарий для строки: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Акт сверки', 'url' => ['actsdoc/view', 'id' => $acts_id]];
//$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Комментировать';
?>
<div class="acts-table-update">

    <?php
        if ($acts_id == 0){
            echo $this->render('_form', ['model' => $model]); 
        } else {
            echo $this->render('_formup', ['model' => $model]); 
        }
    ?>

</div>


