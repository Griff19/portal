<?php
use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Алтайская Буренка';
?>
<div class="site-index">
<!--    <div class="alert-info" align="center"> Вопросы и предложения по работе портала отправляйте на адрес it7@altburenka.ru </div>-->
    <div class="jumbotron">
        <h3>Система онлайн заказа</h3>
        <?= Html::a('Каталог товаров', ['goods/index'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Новый каталог', ['goods/catalog'], ['class' => 'btn btn-default']) ?>
    </div>

    <div class="body-content">

        <div class="row">
            <div class="col-sm-4">
                <?= Html::a('Заказы &raquo', ['orders/index'],['class' => 'btn btn100 btn-default']) ?>
                <p>Здесь вы можете посмотреть свои заказы</p>
            </div>
            <div class="col-sm-4">
                <?= Html::a('Магазины/Отделы &raquo', ['customers/index'],['class' => 'btn btn100 btn-default']) ?>
                <p>Закрепленные за вами магазины/отделы</p>
            </div>
            <div class="col-sm-4">
                <?= Html::a('Акты сверки &raquo', ['actsdoc/index'],['class' => 'btn btn100 btn-default']) ?>
                <p>Здесь вы можете посмотреть акты сверки</p>
            </div>
        </div>

        <?php if (Yii::$app->user->can('admin')){?>
        <div class="row" style="background-color: pink">
            <h4 align="center">Административные функции</h4>
            <div class="col-sm-4">
                <?= Html::a('Изображения', ['images/index'],['class' => 'btn btn100 btn-default']) ?>
                <p>Просмотр загруженных изображений</p>
            </div>
            <div class="col-sm-4">
                <?= Html::a('Почта', ['emails/index'],['class' => 'btn btn100 btn-default']) ?>
                <p>Просмотр и отправка писем по Email</p>
            </div>
        </div>
        <?php }?>
        <div class="row">
            <div class="col-md-12">
                <h3>Информация</h3>
                <p>
                    <span class="glyphicon glyphicon-info-sign"></span> Заявки на продукцию принимаются до 13:00, просроченные заявки обработаются на следующий день.<br>
                    <span class="glyphicon glyphicon-info-sign"></span> Вопросы и предложения по работе портала отправляйте на адрес
                        <?= Html::mailto('it7@altburenka.ru','it7@altburenka.ru') ?><br>
                </p>
            </div>
        </div>

    </div>
</div>
