<?php
/**
 * @link http://laco.pro
 * @copyright Copyright (c) Laco Digital Agency
 * Date: 10.04.2017
 */

namespace laco\uploader\sourceFile;

use yii\helpers\FileHelper;
/**
 * Предпологается использовать для файлов доступных по ссылке
 * Class RemoteFile
 * @package laco\uploader\sourceFile
 */
class RemoteFile extends BaseSourceFile implements SourceFileInterface
{

    /**
     * @return string file extension
     */
    public function getExtension()
    {
        if (!$this->_extension) {
            $this->_extension = parent::getExtension();
            $this->_extension = explode('?', $this->_extension);
            $this->_extension = $this->_extension[0];
            if (!$this->_extension) {
                $headers = get_headers($this->_fullName, 1);
                $this->_extension = isset($headers['Content-Type']) ? end(FileHelper::getExtensionsByMimeType($headers['Content-Type'])) : '';
            }
        }

        return $this->_extension;
    }
}