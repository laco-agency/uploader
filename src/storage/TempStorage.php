<?php
/**
 * @link http://laco.pro
 * @copyright Copyright (c) Laco Digital Agency
 * Date: 11.04.2017
 */

namespace laco\uploader\storage;

use laco\uploader\storageFile\BaseStorageFile;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * Class TempStorage
 */
class TempStorage extends BaseStorage
{
    public $webRootAlias = '@backend/web';
    public $webPathTemplate = 'uploads/temp/{editSessionKey}';
    public $webBaseUrl = '@backendUrl';// нужен AppAliases

    /**
     * хреновое место, костыль с использованием родителя.
     * Возможно стоит сделать отдельный класс для настройки
     * @param $matches
     * @return string
     */
    protected function getPathPlaceholderValue($matches)
    {
        $placeholder = $matches[1];

        if ($placeholder == 'editSessionKey') {
            $placeholder = $this->getEditSessionKey();
        } else {
            $placeholder = parent::getPathPlaceholderValue($matches);
        }

        return $placeholder;
    }


    /**
     * @return string
     */
    public function getEditSessionKey()
    {
        return $this->model->getEditSessionKey();
    }

}