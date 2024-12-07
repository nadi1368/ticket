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
        'js/ajax-modal-popup.js',
        'js/toast.js',
        'js/jquery.double-keypress.js',
        'js/custom.js',
        'js/keyword.js',
        'js/account.js',
        // sweetalert2 JavaScript -->
        'js/sweetalert2.all.min.js',


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
        'css/fonts/iranSansWebFonts/css/style.css',
        'css/fonts/font-awesome/css/all.min.css',
        // 'css/fonts/font-awesome-5/css/all.min.css',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        Bootstrap5Assets::class,
        //'common\assetBundles\ClipboardAsset',
    ];

}
