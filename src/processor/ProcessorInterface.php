<?php

/**
 * @link http://laco.pro
 * @copyright Copyright (c) Laco Digital Agency
 * Date: 27.04.2017
 */

namespace laco\uploader\processor;

use laco\uploader\sourceFile\SourceFileInterface;
use laco\uploader\storageFile\StorageFileInterface;

interface ProcessorInterface
{
    public function setSourceFile(SourceFileInterface $file);

    public function setStorageFile(StorageFileInterface $file);

    public function setOptions($options);

    public function getSuffixes();

    public function run();
}