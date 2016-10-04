<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\grid\Column;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ImagesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Images';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="images-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]);
    Modal::begin([
        'header' => '<div class="breadcrumb"><h4> Комментировать </h4></div>',
        'id' => 'modal',
        'size' => 'modal-lg'
    ]);
    echo '<div id="modalContent"></div>';
    Modal::end();
    // ?>

    <p>
        <?php
        // echo Html::a('Create Images', ['create'], ['class' => 'btn btn-success']);
        if ($id_act !== 0) {
            echo Html::a('<<< Назад в документ', ['actsdoc/view', 'id' => $id_act], ['class' => 'btn btn-success']);
            echo ' ';
            $a = Url::to("/addcomm/".$num."/".$id_act);
            //$a = Url::to("/index.php?r=actstable/update&id=" . $num . "&acts_id=" . $id_act);
            echo Html::button('<< В комментарий', [
                'class' => 'btn btn-primary',
                'name' => 'commentButton',

                'onclick' => 'buttClick("'.$a.'")' //main.js
            ]);
        }
        ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            ['class' => Column::className(),
                'header' => 'Изображение',
                'content' => function($model){
                    return Html::img('/' . $model->img_newname, ['style' => 'width:100px']);
                }
            ],
            'img_oldname',
            'img_newname',
            'img_owner',
            'img_title',

            ['class' => 'yii\grid\ActionColumn',
                'template' => '{view}{delete}',
            ],
//            ['class' => Column::className(),
//                'header' => 'Действие',
//                'content' => function($model) use ($num, $id_act){
//                    if ($id_act !== 0) {
//                        return Html::a('Удалить', ['delete', 'id' => $model->id, 'owner' => $model->img_owner, 'num' => $num, 'id_act' => $id_act], ['data-method' => 'post']);
//                    } else {
//                        return Html::a('Удалить', ['delete', 'id' => $model->id],['data-method' => 'post']);
//                    }
//                }
//            ],
        ],
    ]); ?>

</div>
