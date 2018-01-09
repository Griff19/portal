<?php
/**
 * Начальная страница для работы телефонного оператора
 */
use backend\models\Customers;
use yii\grid\GridView;
use yii\helpers\Html;

/**
 * @var $customerSearch \backend\models\CustomersSearch
 * @var $customerData \yii\data\ActiveDataProvider
 */
?>
<div class="operator-default-index">
    <h1> Страница оператора </h1>
    <?= GridView::widget([
            'dataProvider' => $customerData,
            'filterModel' => $customerSearch,
            'rowOptions' => function ($model) {
                /** @var $model Customers */
                if ($model->orders)
                    return ['class' => 'success'];
                else
                    return null;
            },
            'columns' => [
                'customer_id',
                'customer_1c_id',
                'customer_name',
                'directResponsible',
                ['attribute' => 'directPhone',
                    'value' => function(Customers $model){
                        return Html::a("Заказ", ['order', 'customer_id' => $model->customer_id, 'tp' => $model->typeprices_id], ['class' => 'btn btn-success']);
                    },
                    'format' => 'raw'
                ],
            ],
    ])?>
</div>

<?php /**
 * todo: Наверно надо будет добавить в БД признак текущего контрагента и контрагента в очереди
 */ ?>
