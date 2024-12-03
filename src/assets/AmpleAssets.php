<?php

namespace hesabro\ticket\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AmpleAssets extends AssetBundle
{
    // public $sourcePath = '@backend/web';
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $js = [
        'js/shortcut.js',
        'js/keyword.js',
        'js/ajax-modal-popup.js',
        'js/toast.js',
        'js/custom.js',
        'js/sweetalert2.all.min.js',
        'js/hotkeys.min.js?v=1',
        // ample 7.0
        'ample70/js/app.min.js',
        'ample70/js/app.rtl.init.js',
        'ample70/js/bootstrap.bundle.min.js',
        'ample70/js/feather.min.js',
        'ample70/js/sidebarmenu.js',
        'ample70/js/simplebar.min.js',
        'ample70/js/theme.js',
        //'ample70/js/vendor.min.js',
    ];

    public $css = [
        'ample70/css/styles.css',
        'themify-icons/style.css',
        'css/fonts/iranSansNumber/css/style.css',
        'css/fonts/font-awesome/css/all.min.css',
        // 'css/fonts/font-awesome-5/css/all.min.css',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        Bootstrap5Assets::class,
        //'common\assetBundles\ClipboardAsset',
    ];

}
