<?php

namespace laco\uploader\widgets\tinymce;

use Yii;
use yii\web\View;
use yii\helpers\Json;
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
        'default_link_target' => '_blank',
        'powerpaste_word_import' => 'clean',
        'powerpaste_html_import' => 'merge',
        'file_picker_types' => 'file, image',
        'plugins' => [
            "advlist autolink lists link charmap preview anchor",
            "searchreplace visualblocks code fullscreen image",
            "media table paste"
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
            ['class' => 'redactor-file', 'style' => 'display:none']);

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
        $this->clientOptions['images_upload_handler'] = new \yii\web\JsExpression("
        function (blobInfo, success, failure) {
            var xhr, formData;
        
            xhr = new XMLHttpRequest();
            xhr.withCredentials = false;
            xhr.open('POST', '{$this->uploadUrl}');
        
            xhr.onload = function() {
              var json;
        
              if (xhr.status != 200) {
                failure('HTTP Error: ' + xhr.status);
                return;
              }
        
              json = JSON.parse(xhr.responseText);
        
              if (!json || typeof json.location != 'string') {
                failure('Invalid JSON: ' + xhr.responseText);
                return;
              }
        
              success(json.location);
            };
        
            formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());
            formData.append('uploadFileName','file');
            formData.append('" . Yii::$app->request->csrfParam . "',yii.getCsrfToken());
        
            xhr.send(formData);
          }
        ");

        // @codeCoverageIgnoreStart
        if ($this->language !== null && $this->language !== 'en') {
            $langFile = "langs/{$this->language}.js";
            $langAssetBundle = TinyMceLangAsset::register($view);
            $langAssetBundle->js[] = $langFile;
            $this->clientOptions['language_url'] = $langAssetBundle->baseUrl . "/{$langFile}";
        }
        $uploaderAssetBundle = TinyMceUploaderAsset::register($view);

        // @codeCoverageIgnoreEnd

        $options = Json::encode($this->clientOptions);

        $js[] = "tinymce.init($options);";
        $js[] = "$('#{$id}').parents('form').on('beforeValidate', function() { tinymce.triggerSave(); });";

        $view->registerJs(implode("\n", $js));
        $view->registerJs("var TinyMceDefaultOptions = {$options};", View::POS_HEAD, 'TinyMceDefaultOptions');
    }
}