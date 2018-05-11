<?php
/**
 * @link http://laco.pro
 * @copyright Copyright (c) Laco Digital Agency
 * Date: 10.04.2017
 */

namespace laco\uploader\storage;

use Yii;
use yii\base\BaseObject;
use yii\base\UnknownPropertyException;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

class BaseStorage extends BaseObject implements StorageInterface
{
    /** @var ActiveRecord */
    public $model;

    /**
     * @var string Алиас корневой вэб директории
     */
    public $webRootAlias;

    /**
     * @var string Шаблон для пути относитлеьно корневой вэб директории
     */
    public $webPathTemplate;

    /**
     * @var string базовый URL
     */
    public $webBaseUrl;

    private $_webPath;
    private $_savePath;


    public function getWebPath()
    {
        if (empty($this->_webPath)) {
            $this->_webPath = Yii::getAlias($this->getPath($this->webPathTemplate));
        }
        return $this->_webPath;
    }

    public function getSavePath()
    {
        if (empty($this->_savePath)) {
            $this->_savePath = Yii::getAlias($this->webRootAlias) . DIRECTORY_SEPARATOR . $this->getWebPath();
            $this->_savePath = FileHelper::normalizePath($this->_savePath);
        }
        return $this->_savePath;
    }

    protected function getPath($pathTemplate)
    {
        if (empty($pathTemplate)) {
            return $pathTemplate;
        }
        $result = preg_replace_callback('/{(\^*(\w+))}/', [$this, 'getPathPlaceholderValue'], $pathTemplate);
        return $result;
    }

    protected function getPathPlaceholderValue($matches)
    {
        $placeholderName = $matches[1];
        $placeholderPartSymbolPosition = strspn($placeholderName, '^') - 1;
        if ($placeholderPartSymbolPosition >= 0) {
            $placeholderName = $matches[2];
        }

        switch ($placeholderName) {
            case 'pk': {
                $placeholderValue = $this->getPrimaryKeyStringValue();
                break;
            }
            case '__model__': {
                $placeholderValue = StringHelper::basename(get_class($this->model));
                $placeholderValue = Inflector::camel2id($placeholderValue);
                break;
            }
            default: {
                try {
                    $placeholderValue = $this->model->$placeholderName;
                } catch (UnknownPropertyException $exception) {
                    $placeholderValue = $placeholderName;
                }
            }
        }

        if ($placeholderPartSymbolPosition >= 0) {
            if ($placeholderPartSymbolPosition < strlen($placeholderValue)) {
                $placeholderValue = substr($placeholderValue, $placeholderPartSymbolPosition, 1);
            } else {
                $placeholderValue = '0';
            }
        }
        return $placeholderValue;
    }

    protected function getPrimaryKeyStringValue()
    {
        $primaryKey = $this->model->getPrimaryKey(true);
        return implode('_', $primaryKey);
    }
}