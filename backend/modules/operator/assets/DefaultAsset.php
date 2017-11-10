<?php

namespace backend\modules\operator\assets;

use yii\web\AssetBundle;

class DefaultAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/operator/SIPml.js',
        'js/operator/tsip_api.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
