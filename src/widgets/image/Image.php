<?php

namespace laco\uploader\widgets\image;

use yii\helpers\Json;
use yii\web\View;
use yii\bootstrap\Html;
use yii\widgets\InputWidget;

/**
 * Class TinyMce
 */
class Image extends InputWidget
{
    public $imageOptions = [];
    public $imageUrl;

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('index');
    }


}