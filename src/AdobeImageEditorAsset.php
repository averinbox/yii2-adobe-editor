<?php
/**
 * Created by PhpStorm.
 * User: Andrey Averin
 * Date: 06.12.17
 * Time: 11:44
 */

namespace averinbox\adobeeditor;


use yii\web\AssetBundle;
use yii\web\View;

class AdobeImageEditorAsset extends AssetBundle
{

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];

    public $js = [
        'https://dme0ih8comzn4.cloudfront.net/imaging/v3/editor.js',
    ];


    public $jsOptions = ['position' => View::POS_END];
}