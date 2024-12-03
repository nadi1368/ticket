<?php

namespace backend\modules\employee\assets;

use yii\web\AssetBundle;

class TicketAssets extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $js = [
        'js/shortcut.js',
        'js/keyword.js',
        'js/assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js',
        'js/assets/libs/jquery.repeater/jquery.repeater.min.js',
        'js/assets/libs/sparkline/sparkline.js',
        'js/app.min.js',
        'js/smooth-scroll.min.js',
        YII_DEBUG ? 'js/app.init.mini-sidebar-local.js' : 'js/app.init.mini-sidebar-shop.js',
        'js/waves.js',
        'js/app-style-switcher.js',
        'js/sidebarmenu.js',
        'js/ajax-modal-popup.js',
        'js/toast.js',
        'js/custom.js',
        'js/jquery.double-keypress.js',
        'js/jquery.tagsinput.js',
        'js/sweetalert2.all.min.js',
    ];

    public $css = [
        'themify-icons/style.css',
        'css/fonts/iranSansNumber/css/style.css',
        'css/fonts/font-awesome/css/all.min.css',
        'css/fonts/yekan/style.css',
        'css/custom.css',
        'css/sweetalert2.min.css',
        'css/table-responsive.css',
        'css/employee.css',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapPluginAsset'
    ];
}
