<?php
/**
 * @link http://laco.pro
 * @copyright Copyright (c) Laco Digital Agency
 * Date: 28.04.2017
 */

namespace laco\uploader\storageFile;


use laco\uploader\storage\BaseStorage;
use yii\base\Object;
use yii\helpers\FileHelper;
use yii\helpers\Url;

/**
 * Class BaseStorageFile
 * @property $storage;
 * @property $suffixes;
 */
class BaseStorageFile extends Object implements StorageFileInterface
{
    protected $baseName;
    protected $extension;
    protected $suffixes = [];

    /** @var  BaseStorage */
    private $_storage;


    public function getBaseName()
    {
        if ($this->baseName === null) {
            $this->baseName = $this->getStorage()->getSourceFile() ?
                $this->getStorage()->getSourceFile()->getBaseName()
                : $this->getAttributeBaseName();
        }
        return $this->baseName;
    }

    public function getAttributeBaseName()
    {
        $fileName = $this->getStorage()->model->{$this->getStorage()->attribute};
        return $this->extractBaseName($fileName);
    }

    public function setBaseName($baseName)
    {
        $this->baseName = $baseName;
    }

    public function getExtension()
    {
        if (empty($this->extension)) {
            $this->extension = $this->getStorage()->getSourceFile() ?
                $this->getStorage()->getSourceFile()->getExtension()
                : $this->getAttributeExtension();
        }
        return $this->extension;
    }

    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    public function getAttributeExtension()
    {
        $fileName = $this->getStorage()->model->{$this->getStorage()->attribute};
        return $this->extractExtension($fileName);
    }

    public function getFullName($suffix = '')
    {
        return $this->getStorage()->getSavePath() . DIRECTORY_SEPARATOR . $this->getName($suffix);
    }

    public function getName($suffix = '')
    {
        if ($this->getBaseName() === '') {
            return null;
        }
        if (empty($suffix)) {
            $name = $this->getBaseName() . '.' . $this->getExtension();
        } else {
            $name = $this->getBaseName() . '_' . $suffix . '.' . $this->getExtension();
        }
        return $name;
    }

    public function setName($fileName)
    {
        $this->setBaseName($this->extractBaseName($fileName));
        $this->setExtension($this->extractExtension($fileName));
    }

    public function getUrl($suffix = '')
    {
        if ($this->getName($suffix) === null) {
            return null;
        }
        $url = '/' . $this->getStorage()->getWebPath() . '/' . $this->getName($suffix);
        return Url::to($this->getStorage()->webBaseUrl . $url);
    }

    public function getAllUrls()
    {
        $result = [];
        if ($suffixes = $this->getSuffixes()) {
            foreach ($suffixes as $suffix) {
                $result[$suffix] = $this->getUrl($suffix);
            }
        } else {
            $result[] = $this->getUrl();
        }
        return $result;

    }

    /**
     * @return mixed
     */
    public function getSuffixes()
    {
        if (empty($this->suffixes)) {
            $this->suffixes = $this->getStorage()->getFileSuffixes();
        }

        return $this->suffixes;
    }

    /**
     * @param array $suffixes
     */
    public function setSuffixes($suffixes)
    {
        $this->suffixes = $suffixes;
    }

    /**
     * @return BaseStorage
     */
    public function getStorage()
    {
        return $this->_storage;
    }

    public function setStorage(BaseStorage $storage)
    {
        $this->_storage = $storage;
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
}