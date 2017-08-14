<?php

namespace laco\uploader\widgets\tinymce;

use yii\web\AssetBundle;

class TinyMceUploaderAsset extends AssetBundle
{
    public $sourcePath = '@vendor/laco-agency/uploader/src/assets/';

    public $depends = [
        'laco\uploader\widgets\tinymce\TinyMceAsset'
    ];
}
