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

namespace laco\uploader\behaviors;

use laco\uploader\sourceFile\SourceFileInterface;
use laco\uploader\sourceFile\UploadedFile;
use laco\uploader\storage\TempStorage;
use yii\db\ActiveRecord;
use Yii;
use yii\base\Behavior;
use yii\db\BaseActiveRecord;
use yii\helpers\FileHelper;


class UploadBehavior extends Behavior
{
    public $uploadAttributes;

    private $_editSessionKey;
    private $_tempFiles = [];
    private $_files = [];


    /**
     * Declares events and the corresponding event handler methods.
     * @return array events (array keys) and the corresponding event handler methods (array values).
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
            BaseActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            BaseActiveRecord::EVENT_AFTER_DELETE => 'afterDelete'
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
            if (!($owner->$attribute instanceof SourceFileInterface)) {
                $uploadedFile = UploadedFile::getInstance($owner, $attribute);
                if ($uploadedFile) {
                    $owner->$attribute = $uploadedFile;
                }
            }
        }
    }


    public function beforeInsert($event)
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;

        foreach ($this->uploadAttributes as $attribute => $options) {
            if ($owner->$attribute instanceof SourceFileInterface) {
                if (!$event->isValid = $this->_saveAttributeFileToTemp($attribute)) {
                    break;
                }
                $owner->$attribute = $this->_tempFiles[$attribute]->getName();
            }
        }
    }

    public function beforeUpdate($event)
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        foreach ($this->uploadAttributes as $attribute => $options) {
            if ($owner->$attribute instanceof SourceFileInterface) {
                if (!$event->isValid = $this->_saveAttributeFile($attribute)) {
                    break;
                }
                $owner->$attribute = $this->_files[$attribute]->getName();
            }
        }
    }

    public function afterInsert($event)
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        foreach ($this->_tempFiles as $attribute => $tempFile) {
            $storageFile = $this->getFile($attribute);
            $this->_copyFile($tempFile, $storageFile);
        }
        $this->_deleteEditSessionTemp();
    }

    public function afterUpdate($event)
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;

        foreach ($this->uploadAttributes as $attribute => $options) {
            if (key_exists($attribute, $event->changedAttributes) && !empty($event->changedAttributes[$attribute])) {
                $this->_deleteOldAttributeFile($attribute, $event->changedAttributes[$attribute]);
            }
        }
    }

    public function afterDelete()
    {
        foreach ($this->uploadAttributes as $attribute => $options) {
            if ($file = $this->getFile($attribute)) {
                $file->delete();
            }
        }
    }

    public function getFileUrl($attribute, $suffix = '')
    {
        return $this->getFile($attribute) ? $this->getFile($attribute)->getUrl($suffix) : null;
    }

    public function getFileFullName($attribute, $suffix = '')
    {
        return $this->getFile($attribute) ? $this->getFile($attribute)->getFullName($suffix) : null;
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

    protected function getFile($attribute)
    {
        if (!key_exists($attribute, $this->_files)) {
            $this->_files[$attribute] = $this->_createAttributeFileObject($attribute);
        }
        return $this->_files[$attribute];
    }

    private function _createAttributeFileObject($attribute)
    {
        if (empty($this->uploadAttributes[$attribute]['model'])) {
            $this->uploadAttributes[$attribute]['model'] = $this->owner;
        }
        if (empty($this->uploadAttributes[$attribute]['attribute'])) {
            $this->uploadAttributes[$attribute]['attribute'] = $attribute;
        }
        return Yii::createObject($this->uploadAttributes[$attribute]);
    }

    private function _saveAttributeFile($attribute)
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        $file = $this->getFile($attribute);
        if ($file->save($owner->$attribute)) {
            return true;
        } else {
            $owner->addErrors([$attribute => $file->getErrors()]);
            return false;
        }
    }

    private function _saveAttributeFileToTemp($attribute)
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        $file = $this->_tempFiles[$attribute] = $this->_createAttributeFileObject($attribute);
        $file->storage = new TempStorage(['model' => $this->owner]);
        if ($file->save($owner->$attribute)) {
            return true;
        } else {
            $owner->addErrors([$attribute => $file->getErrors()]);
            return false;
        }
    }

    private function _deleteOldAttributeFile($attribute, $fileName)
    {
        if (empty($fileName)) {
            return;
        }
        $file = $this->_createAttributeFileObject($attribute);
        $file->deleteByFileName($fileName);
    }

    private function _copyFile($fromFile, $toFile)
    {
        FileHelper::createDirectory($toFile->storage->getSavePath());
        if (is_array($fromFile->processOptions)) {
            foreach (array_keys($fromFile->processOptions) as $suffix) {
                @copy($fromFile->getFullName($suffix), $toFile->getFullName($suffix));
            }
        }else{
	        @copy($fromFile->getFullName(), $toFile->getFullName());
        }
    }

    private function _deleteEditSessionTemp()
    {
        $tempStorage = new TempStorage(['model' => $this->owner]);
        $tempPath = $tempStorage->getSavePath();
        FileHelper::removeDirectory($tempPath);
    }
}