<?php
/**
 * Содержимое модального окна, выводимого при переходе к новому контрагенту
 * @var $this \yii\web\View
 * @var $model \backend\modules\operator\models\OperLog
 */
?>

<div>
    <p>Вы не закончили набор заказа, укажите причину для перехода к следующему контрагенту</p>
    <?= $this->renderAjax('/oper-log/_form', ['model' => $model]) ?>
</div>
