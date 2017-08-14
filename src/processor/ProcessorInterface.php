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
    public function run($inputFileFullName, $outputFileFullName);
}