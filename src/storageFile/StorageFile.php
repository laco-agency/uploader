<?php
/**
 * @link http://laco.pro
 * @copyright Copyright (c) Laco Digital Agency
 * Date: 28.04.2017
 */

namespace laco\uploader\storageFile;


use laco\uploader\sourceFile\SourceFileInterface;
use Yii;
use laco\uploader\storage\BaseStorage;
use yii\base\Object;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\helpers\Url;

/**
 * Class BaseStorageFile
 * @property array $processOptions;
 */
class StorageFile extends BaseStorageFile
{

    public $processOptions;
    public $chmod = 0755;

    /**
     * @param $sourceFile SourceFileInterface
     * @return bool
     */
    public function save($sourceFile)
    {
        $this->baseName = $this->getUniqueStorageFileBaseName($sourceFile);
        $this->extension = $sourceFile->getExtension();

        if (is_array($this->processOptions)) {
            return $this->saveWithProcess($sourceFile);
        } else {
            return $this->saveInternal($sourceFile);
        }
    }

    public function delete()
    {
        if (is_array($this->processOptions)) {
            foreach ($this->processOptions as $suffix => $processOption) {
                $this->deleteInternal($suffix);
            }
        } else {
            $this->deleteInternal();
        }

        if ($this->isEmptyDirectory($this->storage->getSavePath())) {
            FileHelper::removeDirectory($this->storage->getSavePath());
        }
    }

    public function deleteByFileName($fileName)
    {
        $this->baseName = $this->extractBaseName($fileName);
        $this->extension = $this->extractExtension($fileName);
        $this->delete();
    }

    public function getFullName($suffix = null)
    {
        return $this->storage->getSavePath() . DIRECTORY_SEPARATOR . $this->getName($suffix);
    }

    public function getName($suffix = null)
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

    public function getUrl($suffix = null)
    {
        if ($this->getName($suffix) === null) {
            return null;
        }
        $url = '/' . $this->storage->getWebPath() . '/' . $this->getName($suffix);
        return Url::to($this->storage->webBaseUrl . $url);
    }

    public function getAllUrls()
    {
        $result = [];
        if (is_array($this->processOptions)) {
            foreach (array_keys($this->processOptions) as $suffix) {
                $result[$suffix] = $this->getUrl($suffix);
            }
        } else {
            $result[] = $this->getUrl();
        }
        return $result;
    }

    protected function isEmptyDirectory($path)
    {
        return !glob($path . '/*');
    }

    /**
     * @param $sourceFile SourceFileInterface
     * @return string
     */
    protected function getUniqueStorageFileBaseName($sourceFile)
    {
        $baseName = $uniqueBaseName = Inflector::slug($sourceFile->getBaseName());
        $ext = $sourceFile->getExtension();
        $i = 0;

        while ($this->isFileExists($uniqueBaseName, $ext)) {
            $uniqueBaseName = $baseName . '_' . $i++;
        }
        return $uniqueBaseName;
    }

    protected function isFileExists($fileBaseName, $fileExtension)
    {
        if (is_file($this->storage->getSavePath() . DIRECTORY_SEPARATOR . $fileBaseName . '.' . $fileExtension)) {
            return true;
        }

        if (is_array($this->processOptions)) {
            foreach (array_keys($this->processOptions) as $suffix) {
                if (is_file($this->storage->getSavePath() . DIRECTORY_SEPARATOR . $fileBaseName . '_' . $suffix . '.' . $fileExtension)) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function deleteInternal($suffix = null)
    {
        if (is_file($this->getFullName($suffix))) {
            unlink($this->getFullName($suffix));
        }
    }

    /**
     * @param $sourceFile SourceFileInterface
     * @return bool
     */
    protected function saveInternal($sourceFile)
    {
        FileHelper::createDirectory($this->storage->getSavePath(), $this->chmod);
        if (!@copy($sourceFile->getFullName(), $this->getFullName())) {
            $this->addError('Невозможно сохранить файл ' . $this->getFullName());
            return false;
        }
        chmod($this->getFullName(), $this->chmod);
        return true;
    }

    /**
     * @param $sourceFile SourceFileInterface
     * @return bool
     */
    protected function saveWithProcess($sourceFile)
    {
        FileHelper::createDirectory($this->storage->getSavePath(), $this->chmod);
        foreach ($this->processOptions as $suffix => $processOption) {
            $processor = Yii::createObject($processOption);
            $processor->run($sourceFile->getFullName(), $this->getFullName($suffix));
            if ($processor->hasErrors()) {
                $this->addErrors($processor->getErrors());
                return false;
            }
        }
        return true;
    }
}