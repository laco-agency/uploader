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

Attach UploadBehavior to model and configere file attributes

```php

use laco\uploader\processor\ImageProcessor;
use laco\uploader\storage\ModelStorage;
use laco\uploader\storageFile\StorageFile;
use laco\uploader\behaviors\UploadBehavior;

class Model extends yii\db\ActiveRecord
{

 public function behaviors()
    {
        return [
            [
                'class' => UploadBehavior::className(),
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


Image file input
-----
In view file
```php
use \laco\uploader\widgets\image\Image;

    <?= $form->field($model, 'image_preview')->widget(Image::className(),
    ['imageUrl' => $model->getFileUrl('image_preview', 'thumb')]); ?>
```


TinyMCE
-----
In view file
```php
use laco\uploader\widgets\tinymce\TinyMce;

<?= $form->field($model, 'content')->widget(TinyMce::className()); ?>
```