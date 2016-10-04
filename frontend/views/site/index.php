<?php

use yii\helpers\Url;
use yii\helpers\Html;
use backend\controllers\SiteController;

/* @var $this yii\web\View */
$this->title = 'Система онлайн заказа';
?>
<div class="site-index">

    <div class="jumbotron">
        
        <p class="lead">Для начала работы войдите в систему</p>

        <p><a class="btn btn-lg btn-success" href="<?= Url::to('/backend/web/')?>">Вход</a></p>
        
    </div>

    <div class="body-content">

        <div class="row">
            <div class="col-lg-4">
                <h2>Заказы</h2>

                <p>Вы сможете делать заказы!</p>

                </div>
            <div class="col-lg-4">
                <h2>Контрагенты</h2>

                <p>Вы сможете завести множество контрагентов для своего аккаунта!!</p>

                </div>
            <div class="col-lg-4">
                <h2>Документы</h2>

                <p>Вы сможете просматривать и скачивать докумены!!!</p>

                </div>
        </div>

    </div>
</div>
