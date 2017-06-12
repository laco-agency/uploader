<?php
/**
 * @link http://laco.pro
 * @copyright Copyright (c) Laco Digital Agency
 * Date: 10.04.2017
 */

namespace laco\uploader\storage;

use laco\uploader\processor\ProcessorInterface;
use laco\uploader\sourceFile\RemoteFile;
use laco\uploader\storageFile\BaseStorageFile;
use laco\uploader\storageFile\StorageFileInterface;
use Yii;
use laco\uploader\sourceFile\SourceFileInterface;
use yii\base\UnknownPropertyException;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

class BaseStorage extends \yii\base\Object
{
    /**
     * @var string Алиас корневой вэб директории
     */
    public $webRootAlias;

    /**
     * @var string Шаблон для пути относитлеьно корневой вэб директории
     */
    public $webPathTemplate;

    /**
     * @var string базовый URL
     */
    public $webBaseUrl;


    private $_webPath;
    private $_savePath;

    /** @var  SourceFileInterface */
    public $_sourceFile;

    /** @var  StorageFileInterface */
    protected $_storageFile;

    /** @var  ProcessorInterface */
    private $_processor;


    /** @var ActiveRecord */
    public $model;
    public $attribute;

    public $chmod = 0755;


    public function getWebPath()
    {
        if (empty($this->_webPath) || $this->model->isNewRecord) {
            $this->_webPath = Yii::getAlias($this->getPath($this->webPathTemplate));
        }
        return $this->_webPath;
    }

    public function getSavePath()
    {
        if (empty($this->_savePath)) {
            $this->_savePath = Yii::getAlias($this->webRootAlias) . DIRECTORY_SEPARATOR . $this->getWebPath();
            $this->_savePath = FileHelper::normalizePath($this->_savePath);
        }
        return $this->_savePath;
    }

    protected function getPath($pathTemplate)
    {
        if (empty($pathTemplate)) {
            return $pathTemplate;
        }
        $result = preg_replace_callback('/{(\^*(\w+))}/', [$this, 'getPathPlaceholderValue'], $pathTemplate);
        return $result;
    }


    protected function getPathPlaceholderValue($matches)
    {
        $placeholderName = $matches[1];
        $placeholderPartSymbolPosition = strspn($placeholderName, '^') - 1;
        if ($placeholderPartSymbolPosition >= 0) {
            $placeholderName = $matches[2];
        }

        switch ($placeholderName) {
            case 'pk': {
                $placeholderValue = $this->getPrimaryKeyStringValue();
                break;
            }
            case '__model__': {
                $placeholderValue = StringHelper::basename(get_class($this->model));
                $placeholderValue = Inflector::camel2id($placeholderValue);
                break;
            }
            default: {
                try {
                    $placeholderValue = $this->model->$placeholderName;
                } catch (UnknownPropertyException $exception) {
                    $placeholderValue = $placeholderName;
                }
            }
        }

        if ($placeholderPartSymbolPosition >= 0) {
            if ($placeholderPartSymbolPosition < strlen($placeholderValue)) {
                $placeholderValue = substr($placeholderValue, $placeholderPartSymbolPosition, 1);
            } else {
                $placeholderValue = '0';
            }
        }
        return $placeholderValue;
    }


    protected function getPrimaryKeyStringValue()
    {
        $primaryKey = $this->model->getPrimaryKey(true);
        return implode('_', $primaryKey);
    }


    public function isFileExists($fileBaseName, $fileExtension, $suffixes = [])
    {
        if (is_file($this->getSavePath() . DIRECTORY_SEPARATOR . $fileBaseName . '.' . $fileExtension)) {
            return true;
        }

        foreach ($suffixes as $suffix) {
            if (is_file($this->getSavePath() . DIRECTORY_SEPARATOR . $fileBaseName . '_' . $suffix . '.' . $fileExtension)) {
                return true;
            }
        }
        return false;
    }


    public function getAttributeFile()
    {
        $baseName = pathinfo($this->model->{$this->attribute}, PATHINFO_FILENAME);
        $baseName = '_' . $baseName;
        $baseName = mb_substr($baseName, 1, mb_strlen($baseName, '8bit'), '8bit');

        $this->getStorageFile()->setBaseName($baseName);
        return $this->getStorageFile();
    }


    public function save()
    {
        FileHelper::createDirectory($this->getSavePath(), $this->chmod);

        if ($processor = $this->getProcessor()) {
            $processor->setSourceFile($this->getSourceFile());
            $processor->setStorageFile($this->getStorageFile());
            return $processor->run() ? $this->getStorageFile() : null;
        } else {
            return $this->_save() ? $this->getStorageFile() : null;
        }
    }

    private function _save()
    {
        $sourceFile = $this->getSourceFile()->getFullName();
        $storageFile = $this->getStorageFile()->getFullName();

        if ($this->getSourceFile() instanceof RemoteFile) {
            if (!@copy($sourceFile, $storageFile)) {
                return false;
            }
        } else {
            if (!@rename($sourceFile, $storageFile)) {
                return false;
            }
        }

        chmod($storageFile, $this->chmod);
        return true;
    }


    public function delete()
    {
        $storageFile = $this->getStorageFile();
        foreach ($storageFile->getSuffixes() as $suffix) {
            if (is_file($storageFile->getFullName($suffix))) {
                unlink($storageFile->getFullName($suffix));
            }
        }
    }

    public function setProcessor($processor)
    {
        if (is_array($processor)) {
            $this->_processor = Yii::createObject($processor);
        } elseif ($processor instanceof ProcessorInterface) {
            $this->_processor = $processor;
        }
    }

    public function getProcessor()
    {
        return $this->_processor;
    }

    public function setStorageFile(StorageFileInterface $file)
    {
        $this->_storageFile = $file;
    }

    public function getStorageFile()
    {
        if (!$this->_storageFile) {
            $this->_storageFile = new BaseStorageFile(['storage' => $this]);
        }
        return $this->_storageFile;
    }

    /**
     * @param $file SourceFileInterface
     */
    public function setSourceFile(SourceFileInterface $file)
    {
        $this->_sourceFile = $file;
        $this->_storageFile = null;
    }

    /**
     * @return SourceFileInterface
     */
    public function getSourceFile()
    {
        return $this->_sourceFile;
    }

    public function getFileSuffixes()
    {
        $suffixes = [];
        if (!empty($this->getProcessor())) {
            $suffixes = $this->getProcessor()->getSuffixes();
        }
        return $suffixes;
    }

}