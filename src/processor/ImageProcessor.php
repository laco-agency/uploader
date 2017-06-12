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
use yii\helpers\ArrayHelper;
use Imagine\Exception\Exception;

class ImageProcessor extends BaseProcessor
{
    public $quality = ['jpeg_quality' => 100, 'png_compression_level' => 9];

    public function run()
    {
        try {
            foreach ($this->getOptions() as $suffix => $options) {
                $this->_convert(
                    $this->getInputFileFullName(),
                    $this->getOutputFileFullName($suffix),
                    $options
                );
            }
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    private function _convert($inputFile, $outputFile, $options)
    {
        $crop = ArrayHelper::getValue($options, 'crop', true);
        $width = ArrayHelper::getValue($options, 'width');
        $height = ArrayHelper::getValue($options, 'height');

        if (!$width || !$height) {
            return;
        }

        $image = (new Imagine())->open($inputFile);
        if ($crop) {
            $this->_resizeWithCrop($image, $width, $height)->save($outputFile, $this->quality);
        } else {
            // ImageInterface::THUMBNAIL_OUTBOUND - размер может вылазить за границы
            // ImageInterface::THUMBNAIL_INSET - обрежет по границе
            $image->thumbnail(new Box($width, $height), ImageInterface::THUMBNAIL_OUTBOUND)->save($outputFile,
                $this->quality);
        }
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

        return $image->resize(new Box($widthI, $heightI))->crop(new Point($widthP, $heightP),
            new Box($width, $height));
    }
}