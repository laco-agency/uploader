<?php
/**
 * @link http://laco.pro
 * @copyright Copyright (c) Laco Digital Agency
 * Date: 30.04.2017
 */

namespace laco\uploader\storageFile;


interface StorageFileInterface
{
    public function getBaseName();

    public function setBaseName($baseName);

    public function getExtension();

    public function getSuffixes();

    public function setSuffixes($suffixes);

    public function getFullName($suffix);

    public function getName($suffix);

    public function getUrl($suffix);

    public function getAllUrls();
}