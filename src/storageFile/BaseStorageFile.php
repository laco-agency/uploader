<?php
/**
 * @link http://laco.pro
 * @copyright Copyright (c) Laco Digital Agency
 * Date: 28.04.2017
 */

namespace laco\uploader\storageFile;

use Yii;
use laco\uploader\storage\BaseStorage;
use yii\base\BaseObject;


/**
 * Class BaseStorageFile
 * @property BaseStorage $storage;
 */
class BaseStorageFile extends BaseObject implements StorageFileInterface
{
    public $model;
    public $attribute;
    /** @var $storage  */
    public $storage;

    protected $baseName;
    protected $extension;

    private $_errors = [];

    public function init()
    {
        parent::init();

        if (!($this->storage instanceof BaseObject)) {
            $this->storage = Yii::createObject($this->storage);
            if (empty($this->storage->model)) {
                $this->storage->model = $this->model;
            }
        }
    }

    public function getBaseName()
    {
        if ($this->baseName === null) {
            $this->baseName = $this->extractBaseName($this->model->{$this->attribute});
        }
        return $this->baseName;
    }

    public function getExtension()
    {
        if ($this->extension === null) {
            $this->extension = $this->extractExtension($this->model->{$this->attribute});
        }
        return $this->extension;
    }

    public function extractExtension($fileName)
    {
        return strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    }

    public function extractBaseName($fileName)
    {
        $pathInfo = pathinfo($fileName, PATHINFO_FILENAME);
        $pathInfo = '_' . $pathInfo;
        return mb_substr($pathInfo, 1, mb_strlen($pathInfo, '8bit'), '8bit');
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function hasErrors()
    {
        return (bool)count($this->_errors);
    }

    public function addErrors($errors)
    {
        foreach ($errors as $error) {
            $this->_errors[] = $error;
        }
    }

    public function addError($error)
    {
        $this->_errors[] = $error;
    }
}