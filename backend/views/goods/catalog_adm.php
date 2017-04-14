<?php

use backend\models\Images;
use backend\models\Goods;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $dataProvider \yii\data\ActiveDataProvider
 */

?>

    <h1>Каталог товаров</h1>

<?php
echo $this->render('_search', ['model' => $searchModel]). '<br/>';

$cols = 3;
$curr_col = 0;
echo '<div class="row">';

foreach ($dataProvider->models as $model) {
    /* @var $model Goods */
    //var_dump($model); continue;
    if ($curr_col > $cols) {
        $curr_col = 0;
        echo '</div><div class="row">';
    }

    $img = $model->image ? '/' . $model->image->img_newname : '/imgs/empty.jpg';
//    if (!$img) {
//        $img = '/imgs/empty.jpg';
//    }
    $title = Images::getTitle(Goods::tableName() . $model->hash_id);
    ?>
    <div class="col-sm-3">

    <div class="thumbnail" style="position: relative; min-height: 250px; <?= $model->status == Goods::DISABLE ? 'opacity: 0.5;' : '' ?> ">
    <?php if ($model->status == Goods::DISCOUNT) { ?>
        <div class="trapezoid rotatable"></div>
        <div class='rotatable pos-top-right'>АКЦИЯ</div>
    <?php } ?>
    <?= Html::img($img, ['alt' => 'Нет изображения']) ?>
    <?php Url::remember(Url::current());
    if (Yii::$app->user->can('operator'))
        echo Html::a('Изменить изображение', ['images/select', 'id_good' => $model->good_id]);
    ?>
    <div class="caption">
    <span style="font-size: smaller"><?= '1c:' . $model->good_1c_id . ' id:' . $model->good_id ?></span><br/>
    <span style="font-size: xx-small"><?= $model->good_detail_guid ?></span>
    <h4><?= Html::a($model->good_name, ['goods/view', 'id' => $model->good_id]) ?></h4>
    <p> <?= $model->good_description ?> </p>
    <p> Тип цены: <?= $model->tPname->type_price_name ?></p>
    <p class="lead"> <?= number_format($model->good_price / 100, 2) . 'p.' ?></p>
    <?php
        if ($model->good_info) {
        echo Html::a('Подробно<span class="caret"></span>', 'javascript:', ['class' => 'inform',
            'data' => ['container' => 'body', 'toggle' => 'popover', 'placement' => 'bottom', 'html' => true,
                'content' => $model->good_info]
        ]);
    }

    echo '</div></div></div>'; // col-sm-3 thumbnail caption

    $curr_col++;
}

echo '</div>'; //row

echo \yii\widgets\LinkPager::widget(['pagination' => $dataProvider->pagination]);

$this->registerJs('$(".inform").popover();');

?>