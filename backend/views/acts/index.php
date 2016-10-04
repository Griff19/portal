<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ActsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Акты';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="acts-index">
    <?php if(Yii::$app->session->hasFlash('success')){
        echo '<div class="alert alert-danger">';
        echo 'В архиве не найдены следующие файлы, указанные в реестре:<br>';
        echo Yii::$app->session->getFlash('success');
        echo '</div>';
    } 
    $dirAct = scandir('Acts/ActsStore');
    if (count($dirAct)>2){
        echo '<div class="alert alert-success">';
        echo 'В файле реестра не указаны документы находящиеся в архиве. Они проигнорированы:<br>';
        foreach($dirAct as $fileAct){
                if($fileAct != '.' && $fileAct!= '..'){
                    echo $fileAct . '<br>';
                    unlink('Acts/ActsStore/' . $fileAct);
                }
        }        
        echo '</div>';
    }
    ?>
    <h1><?= Html::encode($this->title) ?></h1>
    
    <p>
        <?= Html::a('Загрузить акты', ['uploadreestr'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'rowOptions' => function($model){
            return [
                'onclick' => 'window.location.href="'.Url::to('Acts/ActsDoc/'.$model->link).'"; return false',
                'style' => 'cursor:pointer',
            ];
        },
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            'num',
            'begdate',
            'enddate',
            [
                'attribute' => 'users_user_1c_id',
                'value' => 'usersUser.fullname',
            ],
            
//'users_user_1c_id',
            'customers_customer_1c_id',
            'typedoc',
// 'begenddate',

            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
