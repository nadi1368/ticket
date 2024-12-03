<?php

namespace hesabro\ticket\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class Bootstrap5Assets extends AssetBundle
{
    // public $sourcePath = '@backend/web';
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $js = [

    ];

    public $css = [
        'bs5/bootstrap.css',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        //'yii\bootstrap4\BootstrapPluginAsset',
        //'common\assetBundles\ClipboardAsset',
    ];

}
