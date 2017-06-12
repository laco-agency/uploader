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
"laco-agency/uploader": "*"
```

to the require section of your composer.json.

Usage
-----

```php

use laco\uploader\UploaderBehavior;
use laco\uploader\storage\ModelStorage;
use laco\uploader\processor\ImageProcessor;

class Model extends yii\db\ActiveRecord
{

 public function behaviors()
    {
        return [
            [
                'class' => UploaderBehavior::className(),
                'uploadAttributes' => [
                    'image' => [
                        'storage' => [
                            'class' => ModelStorage::className(),
                        ]
                    ],
                    'image_preview' => [
                        'storage' => [
                            'class' => ModelStorage::className(),
                            'processor' => [
                                'class' => ImageProcessor::className(),
                                'options' => [
                                    'origin' => ['width' => 808, 'height' => 455, 'crop' => true],
                                    'thumb' => ['width' => 244, 'height' => 138, 'crop' => true],
                                ]
                            ]
                        ]
                    ],                    
                ]
            ]
        ];
    }
    
    public function rules()
    {
        return [
            [['image'], 'file'],
            [['image_preview'], 'image'],
        ];
    }
}
```