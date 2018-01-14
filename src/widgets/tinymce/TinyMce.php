<?php

namespace laco\uploader\widgets\tinymce;

use yii\helpers\Json;
use yii\web\View;
use yii\bootstrap\Html;
use yii\widgets\InputWidget;

/**
 * Class TinyMce
 */
class TinyMce extends InputWidget
{
    public $registerScript = true;

    public $language = 'ru';

    public $uploadUrl = '/uploader/upload/common';

    public $options = ['rows' => 10, 'class' => 'tinymce-element'];

    public $clientOptions = [
        'menubar' => false,
        'default_link_target'=> '_blank',
        'powerpaste_word_import' => 'clean',
        'powerpaste_html_import' => 'merge',
        'file_browser_callback_types' => 'file image',
        'plugins' => [
            "advlist autolink lists link charmap preview anchor",
            "searchreplace visualblocks code fullscreen image imagetools",
            "media table contextmenu paste"
        ],
        'toolbar' => 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | image media link table | removeformat code fullscreen imagetools'
    ];

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->hasModel()) {
            echo Html::activeTextarea($this->model, $this->attribute, $this->options);
        } else {
            echo Html::textarea($this->name, $this->value, $this->options);
        }

        echo Html::fileInput('redactor-file', null,
            ['class' => 'redactor-file', 'data-upload-url' => $this->uploadUrl, 'style' => 'display:none']);

        if ($this->registerScript) {
            $this->registerClientScript();
        }
    }

    /**
     * Registers tinyMCE js plugin
     */
    protected function registerClientScript()
    {
        $js = [];
        $view = $this->getView();

        TinyMceAsset::register($view);

        $id = $this->options['id'];

        $this->clientOptions['selector'] = "#$id";
        $this->clientOptions['file_browser_callback'] = new \yii\web\JsExpression("function(field_name, url, type, win) {Uploader.browserFileCallback('#" . $id . "', field_name, type)}");

        // @codeCoverageIgnoreStart
        if ($this->language !== null && $this->language !== 'en') {
            $langFile = "langs/{$this->language}.js";
            $langAssetBundle = TinyMceLangAsset::register($view);
            $langAssetBundle->js[] = $langFile;
            $this->clientOptions['language_url'] = $langAssetBundle->baseUrl . "/{$langFile}";
        }
        $uploaderAssetBundle = TinyMceUploaderAsset::register($view);
        $uploaderAssetBundle->js[] = 'js/uploaderWidget.js';
        $this->clientOptions['uploader'] = $uploaderAssetBundle->baseUrl . '/js/uploaderWidget.js';
        // @codeCoverageIgnoreEnd

        $options = Json::encode($this->clientOptions);

        $js[] = "tinymce.init($options);";
        $js[] = "$('#{$id}').parents('form').on('beforeValidate', function() { tinymce.triggerSave(); });";

        $view->registerJs(implode("\n", $js));
        $view->registerJs("var TinyMceDefaultOptions = {$options};", View::POS_HEAD, 'TinyMceDefaultOptions');
    }
}