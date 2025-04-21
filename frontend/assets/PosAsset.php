<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class PosAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css',
        'css/modern-pos.css',
    ];
    
    public $js = [
        'https://code.jquery.com/jquery-3.5.1.min.js',
        'https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js',
        'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js',
        'js/pos.js',
    ];
    
    public $depends = [
        'yii\web\YiiAsset',
    ];
    
    public $jsOptions = [
        'position' => \yii\web\View::POS_HEAD
    ];
}