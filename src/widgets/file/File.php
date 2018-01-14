<?php

namespace laco\uploader\widgets\file;

use yii\helpers\Json;
use yii\web\View;
use yii\bootstrap\Html;
use yii\widgets\InputWidget;

/**
 * Class File
 */
class File extends InputWidget
{
    public $ptions = [];
    public $url;

    /**
     * @inheritdoc
     */
    public function run()
    {
        parent::run();
        $this->field->form->options['enctype'] = 'multipart/form-data';
        echo $this->render('index');
    }
}