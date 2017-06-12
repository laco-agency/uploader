<?php
/**
 * @link http://laco.pro
 * @copyright Copyright (c) Laco Digital Agency
 * Date: 11.04.2017
 */

namespace laco\uploader\storage;

use laco\uploader\sourceFile\SourceFileInterface;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;

class ModelStorage extends BaseStorage
{
    public $webRootAlias = '@frontend/web';
    public $webPathTemplate = 'uploads/{__model__}/{^^pk}/{^pk}/{pk}';
    public $webBaseUrl = '@frontendUrl'; // нужен AppAliases


    /** @var  TempStorage */
    private $_tempStorage;

    public function getTempStorage()
    {
        if (empty($this->_tempStorage)) {
            $this->_tempStorage = new TempStorage(['model' => $this->model, 'attribute' => $this->attribute]);
            if ($this->model->{$this->attribute} instanceof SourceFileInterface) {
                $this->_tempStorage->setSourceFile($this->model->{$this->attribute});
            }
        }
        return $this->_tempStorage;
    }

    public function moveFromTemp()
    {
        FileHelper::createDirectory($this->getSavePath(), $this->chmod);
        $tempFile = $this->getTempStorage()->getStorageFile();
        $storageFile = $this->getStorageFile();

        if ($this->getFileSuffixes()) {
            foreach ($this->getFileSuffixes() as $suffix) {
                if (!@rename($tempFile->getFullName($suffix), $storageFile->getFullName($suffix))) {
                    return false;
                }
            }
        } else {
            if (!@rename($tempFile->getFullName(), $storageFile->getFullName())) {
                return false;
            }
        }
        return true;
    }

    public function save()
    {
        if ($this->model->{$this->attribute} instanceof SourceFileInterface) {
            $this->setSourceFile($this->model->{$this->attribute});
            $this->getTempStorage()->getStorageFile()->setBaseName($this->getUniqueStorageFileBaseName());
            $this->getTempStorage()->setProcessor($this->getProcessor());
            if ($this->getTempStorage()->save()) {
                return $this->getTempStorage()->getStorageFile();
            } else {
                return null;
            }
        } else {
            return $this->moveFromTemp() ? $this->getTempStorage() : null;
        }
    }


    protected function getUniqueStorageFileBaseName()
    {
        $baseName = $fileName = Inflector::slug($this->getSourceFile()->getBaseName());
        $ext = $this->getSourceFile()->getExtension();
        $i = 0;

        while ($this->getTempStorage()->isFileExists($fileName, $ext, $this->getFileSuffixes())
            || $this->isFileExists($fileName, $ext, $this->getFileSuffixes())) {
            $fileName = $baseName . '_' . $i++;
        }
        return $fileName;
    }

}