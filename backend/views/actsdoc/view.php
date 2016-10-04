<?php

use backend\models\Images;
use backend\models\ActsTable;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model backend\models\ActsDoc */

$this->title = 'Акт сверки №' . $model->num_act;
$this->params['breadcrumbs'][] = ['label' => 'Акты сверок', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="acts-doc-view">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= Html::a('Скачать документ', ['actsdoc/download', 'type' => $model->type_act, 'num' => $model->num_act], ['class' => 'btn btn-primary'])?>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            //'id_doc',
            //'type_act',
            [
                'attribute' => 'type_act',
                'value' => $model->type_act == 0 ? 'Взаиморасчеты' : 'Сверка тары',
            ],
            'begdate',
            'enddate',
            //'contr_doc',
            [
                'attribute' => 'contr_doc',
                'value' => '['. $model->contr_doc. '] ' .$model->customer->customer_name,
            ],
            //'control',
            //'user_id',
            [
                'attribute' => 'user_id',
                'value' => $model->user->username,
            ],
            [
                'attribute' => 'beg_sald',
                'value' => $model->beg_sald / 100,
            ],
            [
                'attribute' => 'end_sald',
                'value' => $model->end_sald / 100,
            ],
        ],
    ]) ?>
    
    <div class="actstable-index">
    <?php

    $i = 0; //считаем строки
    ?>
    <?php Pjax::begin();
        //Заворачиваем модальное окно в пиджак, иначе при закрытии окна по "крестику" перестают
        // работать скрипты пагинации
        Modal::begin([
            'header' => '<div class="breadcrumb"><h4> Комментировать </h4></div>',
            'id' => 'modal',
            'size' => 'modal-lg'
        ]);
        echo '<div id="modalContent"></div>';
        Modal::end();
    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'tableOptions' => ['class' => 'table-bordered'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn',
                'contentOptions' => ['style' => 'width:25px'],
            ],
            [
                'attribute' => 'act_num',
                'value' => 'act_num',
                'contentOptions' => ['style' => 'width:100px'],
            ],
            [
                'attribute' => 'date_doc',
                //'value' => 'date_doc',
                'contentOptions' => ['style' => 'width:83px'],
            ],
            [
                'attribute' => 'num_doc',
                'contentOptions' => ['style' => 'width:100px'],
            ],
            [
                'attribute' => 'name_doc',
                'contentOptions' => ['style' => 'width:222px'],
            ],
            [
                'attribute' => 'beg_sald',
                'value' => function ($model){
                    return $model->beg_sald / 100;
                },
                'contentOptions' => ['style' => 'width:90px'],
            ],
            [
                'attribute' => 'end_sald',
                'value' => function ($model){
                    return $model->end_sald / 100; 
                },
                'contentOptions' => ['style' => 'width:90px'],
            ],
            [
                'attribute' => 'cod_good',
                'value' => function ($model){
                    return $model->cod_good;
                },
                'contentOptions' => ['style' => 'width:70px'],
            ],
            [
                'attribute'=>'actstable_comm',
                'format' => 'raw',
                'value'=> function ($model){
                    $img = Images::getImage(Actstable::tableName() . $model->num_doc);

                    if (!empty($img)){
                        return '<a name = "info" data-img = "'.$img.'" data-placement = "left">'.$model->actstable_comm.'</a>';
                    } else {
                        return $model->actstable_comm;
                    }
                },
                'contentOptions' => ['style' => 'position:relative'],
            ],
            ['class' => 'yii\grid\Column',
                'header' => '',
                'content' => function ($mod) use ($model, $dataProvider, &$i){
                    if ($model->control == 0){    
                        $page = $dataProvider->pagination->page + 1;
                        $bm = 'str_'.($i - 3 < 0 ? 0 : $i - 3); //создам ссылку на метку
                        $str = 'str_'. $i++; //создаем метку

                        //$a = Url::to('index.php?r=actstable/update&id='.$mod->id.'&acts_id='.$model->id_doc.'&page='.$page.'&bm='.$bm);
                        $a = Url::to("/addcomm/".$mod->num_doc."/".$model->id_doc."/".$page."/".$bm);
                        return
                            '<a name = '.$str.'></a>' . //чтобы при закрытии модального окна страница открывалась на месте последнего комметария
                            Html::button('Комментарий', [
                            'class' => 'btn btn-primary',
                            'name' => 'commentButton_' . $mod->id,
                            'style' => 'padding: 0px 7px',
                            'onclick' => 'buttClick("'.$a.'")' //main.js
                            ]);
                    }else{
                        return '';
                    }
                },
                'contentOptions' => ['style' => 'width:100px'],        
            ],
        ],
    ]);
    ?>
    <?php $this->registerJs('showInfo()');?>
    <?php Pjax::end(); ?>
</div>
    <br>
        <?php
            if($model->control == 0){
                ///echo Html::a('Отправить оператору', ['actsdoc/dbtofile', 'id' => $model->id_doc], ['class' => 'btn btn-success']);
                echo Html::a('Отправить оператору', ['actsdoc/control', 'id'=>$model->id_doc], ['class' => 'btn btn-success']);
            }else{
                //echo Html::a('Отправить оператору', ['actsdoc/dbtofile', 'id' => $model->id_doc], ['class' => 'btn btn-success']);
                echo Html::a('Вернуть на редактирование', ['actsdoc/control', 'id'=>$model->id_doc], ['class' => 'btn btn-success']);
            }                
        ?>
</div>


