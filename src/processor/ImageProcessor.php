<?php
/**
 * @link http://laco.pro
 * @copyright Copyright (c) Laco Digital Agency
 * Date: 27.04.2017
 */

namespace laco\uploader\processor;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use yii\base\Object;
use yii\helpers\ArrayHelper;
use Imagine\Exception\Exception;

class ImageProcessor extends Object implements ProcessorInterface
{
    public $width;
    public $height;
    public $crop;
    public $quality = ['jpeg_quality' => 100, 'png_compression_level' => 9];
    private $_errors = [];

    public function run($inputFileFullName, $outputFileFullName)
    {
        if (!$this->width || !$this->height) {
            return true;
        }
        try {
            $image = (new Imagine())->open($inputFileFullName);
            if ($this->crop) {
                $this->_resizeWithCrop($image, $this->width, $this->height)->save($outputFileFullName, $this->quality);
            } else {
                // ImageInterface::THUMBNAIL_OUTBOUND - размер может вылазить за границы
                // ImageInterface::THUMBNAIL_INSET - обрежет по границе
                $image->thumbnail(new Box($this->width, $this->height))->save($outputFileFullName, $this->quality);
            }
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Resize by smaller side of image and then centred crop it
     * @param $image ImageInterface
     * @param $width
     * @param $height
     * @return ImageInterface
     */
    private function _resizeWithCrop($image, $width, $height)
    {
        $widthI = $width;
        $heightI = $height;

        $ratio = max($heightI / $image->getSize()->getHeight(), $widthI / $image->getSize()->getWidth());
        $heightI = round($image->getSize()->getHeight() * $ratio);
        $widthI = round($image->getSize()->getWidth() * $ratio);

        $widthP = max(0, round(($widthI - $width) / 2));
        $heightP = max(0, round(($heightI - $height) / 2));

        return $image->resize(new Box($widthI, $heightI))->crop(new Point($widthP, $heightP), new Box($width, $height));
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function hasErrors()
    {
        return (bool)count($this->_errors);
    }

    public function addError($error)
    {
        $this->_errors[] = $error;
    }
}