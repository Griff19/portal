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

    if ($curr_col > $cols) {
        $curr_col = 0;
        echo '</div><div class="row">';
    }

    $img = $model->imgOwn ?  : '/imgs/empty.jpg';

    $title = Images::getTitle(Goods::tableName() . $model->hash_id);
    ?>
    <div class="col-sm-3">

    <div class="thumbnail" style="position: relative; min-height: 250px;">
    <?php if ($model->status == Goods::DISCOUNT) { ?>
        <div class="trapezoid rotatable"></div>
        <div class='rotatable pos-top-right'>АКЦИЯ</div>
    <?php } ?>
    <?= Html::img($img, ['alt' => 'Нет изображения', 'style' => 'max-height: 200px']) ?>
    <div class="caption">
    <?= Html::a('<h4>' . $model->good_name . '</h4>', ['goods/view', 'id' => $model->good_id]) ?>
    <p> <?= $model->good_description . '</p>';
    echo '<p class="lead">' . number_format($model->good_price / 100, 2) . 'p.</p>';
    if ($model->good_info) {
        echo Html::a('Подробно<span class="caret"></span>', 'javascript:', ['class' => 'inform',
            'data' => ['container' => 'body', 'toggle' => 'popover', 'placement' => 'bottom', 'html' => true,
                'content' => $model->good_info]
        ]);
    }
    echo '<div class="input-group">';
    echo Html::input('number', 'count' . $model->good_id, 0, ['class' => 'form-control', 'id' => $model->good_id,
            'style' => 'padding: 6px 6px'
        ]);
    echo '<span class="input-group-btn">';
    echo Html::a('Заказать', 'javascript:', ['class' => 'btn btn-success',
        'onclick' => '$.get("basketadd/'. $model->hash_id .'/"+$("#'.$model->good_id.'").val(), function (data) {
            if (!data) return false;
            $("#getTotals a").text(data);
            $("#'.$model->good_id.'").val(0);
            });'
        ]);
    echo '</span></div>'; // input-group-btn input-group
    echo '</div></div></div>'; // col-sm-3 thumbnail caption

    $curr_col++;
}

echo '</div>'; //row

$this->registerJs('$(".inform").popover();');

?>