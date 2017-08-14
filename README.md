# uploader
yii2 file uploader

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist laco-agency/uploader
```

or add

```json
"laco-agency/uploader":"*"
```

to the require section of your composer.json.

Usage
-----
Add module to config file

```php

 'uploader' => ['class' => '\laco\uploader\Module'],
``` 

Attach UploaderBehavior to model and configere file attributes

```php

use laco\uploader\processor\ImageProcessor;
use laco\uploader\storage\ModelStorage;
use laco\uploader\storageFile\StorageFile;
use laco\uploader\behaviors\UploadBehaviour;

class Model extends yii\db\ActiveRecord
{

 public function behaviors()
    {
        return [
            [
                'class' => UploaderBehavior::className(),
                'uploadAttributes' => [
                [
                'class' => UploadBehaviour::className(),
                'uploadAttributes' => [
                    'image_preview' => [
                        'class' => StorageFile::className(),
                        'storage' => ModelStorage::className(),
                        'processOptions' => [
                            'origin' => [
                                'class' => ImageProcessor::className(),
                                'width' => 912,
                                'height' => 570,
                                'crop' => true
                            ],
                            'thumb' => [
                                'class' => ImageProcessor::className(),
                                'width' => 244,
                                'height' => 138,
                                'crop' => true
                            ],
                        ]
                    ],                   
                ]
            ]
        ];
    }
 
   
   // Configure validation rules for files attributes as regular   
    public function rules()
    {
        return [
            [['image_preview'], 'image'],
        ];
    }
}
```

TinyMCE
-----
In view file
```php
use laco\uploader\widgets\tinyMce\TinyMce;

<?= $form->field($model, 'content')->widget(TinyMce::className()); ?>
```