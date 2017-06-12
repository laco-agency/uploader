<?php
/**
 * @link http://laco.pro
 * @copyright Copyright (c) Laco Digital Agency
 * Date: 25.02.2017
 *
 * При разработке использовались репы:
 * @link https://github.com/yii2tech/ar-file
 * @link https://github.com/paulzi/yii2-file-behavior
 */

namespace laco\uploader;

use laco\uploader\storage\BaseStorage;
use laco\uploader\storage\TempStorage;
use laco\uploader\sourceFile\UploadedFile;
use laco\uploader\sourceFile\SourceFileInterface;

use Yii;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\helpers\FileHelper;

use yii\base\Behavior as BaseBehavior;

class UploaderBehavior extends BaseBehavior
{
    public $uploadAttributes;

    private $_editSessionKey;


    /**
     * Declares events and the corresponding event handler methods.
     * @return array events (array keys) and the corresponding event handler methods (array values).
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            BaseActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            //BaseActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }


    public function beforeValidate($event)
    {
        $this->autoLoadFileInstances();
    }

    protected function autoLoadFileInstances()
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        foreach ($this->uploadAttributes as $attribute => $options) {
            if (!$owner->$attribute instanceof SourceFileInterface) {
                $uploadedFile = UploadedFile::getInstance($owner, $attribute);
                if ($uploadedFile) {
                    $owner->$attribute = $uploadedFile;
                }
            }
        }
    }


    public function beforeSave($event)
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;

        foreach ($this->uploadAttributes as $attribute => $options) {
            if (!empty($owner->$attribute) && $owner->isAttributeChanged($attribute)) {
                if ($storageFile = $this->saveAttributeFile($attribute)) {
                    $owner->$attribute = $storageFile->getName();
                } else {
                    $event->isValid = false;
                    break;
                }
            }
        }
    }


    public function afterSave($event)
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;

        foreach ($this->uploadAttributes as $attribute => $options) {
            if (!empty($owner->$attribute) && key_exists($attribute, $event->changedAttributes)) {
                $this->saveAttributeFile($attribute);
                $this->deleteOldAttributeFile($attribute, $event->changedAttributes[$attribute]);
            }
        }
        $this->_deleteEditSessionTemp();
    }

    public function saveAttributeFile($attribute)
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;

        $storage = $this->_getAttributeStorage($attribute);
        if ($storageFile = $storage->save()) {
            return $storageFile;
        } else {
            $owner->addError($attribute, 'Невозможно сохранить файл: ' . $storage->getStorageFile()->getName());
            return null;
        }
    }

    public function deleteOldAttributeFile($attribute, $fileName)
    {
        if (empty($fileName)) {
            return;
        }
        $storage = $this->_getAttributeStorage($attribute);
        $storage->getStorageFile()->setName($fileName);
        $storage->delete();
    }

    private function _getAttributeStorage($attribute)
    {
        /** @var BaseStorage $storage */
        $storage = Yii::createObject($this->uploadAttributes[$attribute]['storage']);
        $storage->model = $this->owner;
        $storage->attribute = $attribute;

        return $storage;
    }

    public function getFileUrl($attribute, $suffix = '')
    {
        return $this->_getAttributeStorage($attribute)->getAttributeFile()->getUrl($suffix);
    }

    public function getEditSessionKey()
    {
        if (empty($this->_editSessionKey)) {

            if (Yii::$app->request->isConsoleRequest) {
                $this->_editSessionKey = Yii::$app->getSecurity()->generateRandomString();
            } else {
                $this->_editSessionKey = request()->post('editSessionKey',
                    Yii::$app->getSecurity()->generateRandomString());
            }

        }

        return $this->_editSessionKey;
    }

    public function setEditSessionKey($_editSessionKey)
    {
        $this->_editSessionKey = $_editSessionKey;
    }

    private function _deleteEditSessionTemp()
    {
        $tempStorage = new TempStorage(['model' => $this->owner]);
        $tempPath = $tempStorage->getSavePath();
        FileHelper::removeDirectory($tempPath);
    }
}