<?php
/**
 * @link http://laco.pro
 * @copyright Copyright (c) Laco Digital Agency
 * Date: 11.04.2017
 */

namespace laco\uploader\storage;

class CommonStorage extends BaseStorage
{
    public $webRootAlias = '@frontend/web';
    public $webPathTemplate = 'uploads/common';
    public $webBaseUrl = '@frontendUrl'; // нужен AppAliases
}