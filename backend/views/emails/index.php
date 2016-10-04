<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 28.05.2016
 * Time: 22:36
 */
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Почта';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="emails-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= Html::a('Написать', ['emails/create'], ['class' => 'btn btn-primary'])?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'receiver_name',
            'receiver_email',
            'subject',
            'content_email',
            'attach',
            ['class' => \yii\grid\ActionColumn::className(),
                'template' => '{view}{delete}'
            ]
        ]
    ])

    ?>
</div>

