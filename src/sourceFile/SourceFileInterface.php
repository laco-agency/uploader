<?php
namespace laco\uploader\sourceFile;


/**
 * @link http://laco.pro
 * @copyright Copyright (c) Laco Digital Agency
 * Date: 10.04.2017
 */
interface SourceFileInterface
{

    public function getFullName();

    public function getBaseName();

    public function getExtension();

}