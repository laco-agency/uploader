<?php
/**
 * @link http://laco.pro
 * @copyright Copyright (c) Laco Digital Agency
 * Date: 10.04.2017
 */

namespace laco\uploader\sourceFile;

/**
 * Для файлов загружаемых через форму
 * Class UploadedFile
 */
class UploadedFile extends \yii\web\UploadedFile implements SourceFileInterface
{
    public function getFullName()
    {
        return $this->tempName;
    }


}