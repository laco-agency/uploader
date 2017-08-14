<?php

namespace laco\uploader\widgets\tinymce;

use yii\web\AssetBundle;

class TinyMceLangAsset extends AssetBundle
{
    public $sourcePath = '@vendor/laco-agency/uploader/src/widgets/tinymce/assets/';

    public $depends = [
        'laco\uploader\widgets\tinymce\TinyMceAsset'
    ];
}
