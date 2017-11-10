<?php

use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\widgets\Alert;
use backend\models\Basket;
use backend\models\Site;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<?php 
    echo '<html lang="'. Yii::$app->language .'">'
?>
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
    <?php $this->beginBody() ?>
    <div class="wrap">
        <?php
            NavBar::begin([
                'brandLabel' => 'Алтайская Буренка',
                'brandUrl' => Yii::$app->homeUrl,
                'options' => [
                    'class' => 'navbar-inverse navbar-fixed-top',
                    'style' => 'position: fixed',
                ],
            ]);
            $menuItems = [
                ['label' => 'Главная', 'url' => ['/site/index']]
            ];
            if (Yii::$app->user->isGuest) {
                $menuItems[] = ['label' => 'Авторизация', 'url' => ['/site/login']];
            } else {
                $menuItems[] = ['label' => 'Заказы', 'url' => ['/orders/index']];
                $menuItems[] = ['label' => 'Заказ '. Basket::getTotals('summ')/100 .'р.',
                                'url' => ['/basket/index'],
                                'options' => ['style' => 'font-weight:bolder', 'id' => 'getTotals']
                                ];
                $menuItems[] = ['label' => 'Магазины', 'url' => ['/customers/index']];
                $menuItems[] = ['label' => 'Акты сверки', 'url' => ['/actsdoc/index'], 'visible' => Yii::$app->user->can('operator')];
                $menuItems[] = [
                    'label' => 'Администрирование',
                    'items' => [
	                    ['label' => 'Модуль Оператора', 'url' => ['/operator']],
                        ['label' => 'Пользователи', 'url' => ['/user/index']],
                        ['label' => 'Изображения', 'url' => ['/images/index']],
                        ['label' => 'Почта', 'url' => ['/emails/index']]
                    ],
                    'visible' => Yii::$app->user->can('admin')
                ];

                $menuItems[] = [
                    'label' => Yii::$app->user->identity->fullname,
                    'items' => [
                        ['label' => 'Мой аккаунт', 'url' => ['/user/view', 'id' => Yii::$app->user->id]],
                        ['label' => 'Выход', 'url' => ['/site/logout'],
                        'linkOptions' => ['data-method' => 'post']]
                    ]];
            }

            echo Nav::widget([
                'options' => ['class' => 'navbar-nav navbar-right'],
                'items' => $menuItems,
            ]);
            NavBar::end();
        ?>

        <div class="container">
            <script>
                if (navigator.userAgent.indexOf('MSIE') >= 0 || navigator.userAgent.indexOf('.NET') >= 0) {
                    document.writeln(
                        "<div class='alert alert-danger'>" +
                        "Внимание! При использовании данного браузера возможна не корректная работа системы.<br/>" +
                        "Для корректной работы рекомендуем использовать браузер " +
                        "<a href='https://www.google.ru/chrome/browser/desktop/index.html' target='_blank'>Google Chrome</a>" +
                        "</div>"
                    );
                }
            </script>
            <?= Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>

        <?= Alert::widget() ?>    
        <?= $content ?>
        <?php
        //если сайт закрыт для пользователей отменяем авторизацию
        if (Site::$deny) {
            if (!Yii::$app->user->can('admin')) {
                Yii::$app->user->logout();
            }
        }
        ?>
        </div>
    </div>

    <footer class="footer">
            <div class="container">
                <p class="text-center">
                    <?= Html::mailto('Вопросы и предложения по работе портала отправляйте на адрес it7@altburenka.ru','it7@altburenka.ru'); ?>
                </p>
                <p class="pull-left">&copy; Алтайская Буренка <?= date('Y') ?></p>
                <p class="pull-right"><?= Yii::powered() ?></p>
            </div>
    </footer>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
