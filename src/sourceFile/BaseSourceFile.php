<?php
/**
 * @link http://laco.pro
 * @copyright Copyright (c) Laco Digital Agency
 * Date: 10.04.2017
 */

namespace laco\uploader\sourceFile;

use Yii;
use yii\base\Object;

/**
 *
 * @property string $extension
 * @property string $baseName
 * @property string $fullName
 */
class BaseSourceFile extends Object implements SourceFileInterface
{
    protected $_baseName;
    protected $_fullName;
    protected $_extension;

    public function setFullName($path)
    {
        $this->_fullName = Yii::getAlias($path);
    }

    public function getFullName()
    {
        return $this->_fullName;
    }

    /**
     * @return string original file base name
     */
    public function getBaseName()
    {
        if (!$this->_baseName) {
            $pathInfo = pathinfo($this->getFullName(), PATHINFO_FILENAME);
            $pathInfo = '_' . $pathInfo;
            $this->_baseName = mb_substr($pathInfo, 1, mb_strlen($pathInfo, '8bit'), '8bit');
        }
        return $this->_baseName;
    }

    /**
     * @return string file extension
     */
    public function getExtension()
    {
        if (!$this->_extension) {
            $this->_extension = strtolower(pathinfo($this->_fullName, PATHINFO_EXTENSION));
        }
        return $this->_extension;
    }
}