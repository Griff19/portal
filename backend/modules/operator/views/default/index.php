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
            'columns' => [
                'customer_id',
                'customer_1c_id',
                'customer_name',
                'directResponsible',
                ['attribute' => 'directPhone',
                    'value' => function(Customers $model){
                        return Html::a("Позвонить", ['order', 'customer_id' => $model->customer_id, 'tp' => $model->typeprices_id], ['class' => 'btn btn-success']);
                    },
                    'format' => 'raw'
                ],
            ],
    ])?>
</div>

<?php /** todo: Настроить таблицу контрагентов так, чтобы был активен только первый в списке, а остальные с затемнением.
 * todo: Наверно надо будет добавить в БД признак текущего контрагента и контрагента в очереди
 * todo: Создать таблицу в БД для хранения привязки контрагентов к конкретному телефонному оператору.
 * todo: Рассмотреть механизм когда оператор не может работать и его контрагенты распределяются на других операторов.
 */ ?>
