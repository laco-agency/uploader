<?php
/**
 * @link http://laco.pro
 * @copyright Copyright (c) Laco Digital Agency
 * Date: 27.04.2017
 */

namespace laco\uploader\processor;

use laco\uploader\sourceFile\SourceFileInterface;
use laco\uploader\storageFile\StorageFileInterface;
use yii\base\Object;

abstract class BaseProcessor extends Object implements ProcessorInterface
{
    /** @var  array */
    private $_options;

    /** @var  SourceFileInterface */
    private $_sourceFile;

    /** @var  StorageFileInterface */
    private $_storageFile;


    abstract function run();

    public function setSourceFile(SourceFileInterface $sourceFile)
    {
        $this->_sourceFile = $sourceFile;
    }

    public function setStorageFile(StorageFileInterface $storageFile)
    {
        $this->_storageFile = $storageFile;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->_options = $options;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->_options ? $this->_options : [];
    }


    public function getSuffixes()
    {
        return array_keys($this->getOptions());
    }

    public function getSourceFile()
    {
        return $this->_sourceFile;
    }

    public function getStorageFile()
    {
        return $this->_storageFile;
    }


    protected function getInputFileFullName()
    {
        return $this->_sourceFile->getFullName();
    }

    protected function getOutputFileFullName($suffix = '')
    {
        return $this->_storageFile->getFullName($suffix);
    }
}