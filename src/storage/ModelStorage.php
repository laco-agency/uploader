<?php
/**
 * @link http://laco.pro
 * @copyright Copyright (c) Laco Digital Agency
 * Date: 11.04.2017
 */

namespace laco\uploader\storage;

class ModelStorage extends BaseStorage
{
    public $webRootAlias = '@frontend/web';
    public $webPathTemplate = 'uploads/{__model__}/{^^pk}/{^pk}/{pk}';
    public $webBaseUrl = '@frontendUrl'; // нужен AppAliases
}