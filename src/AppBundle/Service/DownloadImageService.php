<?php

namespace AppBundle\Service;


class DownloadImageService
{
    private $currentWight;
    private $currentHeight;

    /**
     * @param $name
     * @param $wight
     * @param $height
     * @return bool|resource
     * @throws \Exception
     */
    public function fetchImage($name, $wight, $height)
    {
        $currentImage = imagecreatefromjpeg($name . '.jpg');
        list($this->currentWight, $this->currentHeight) = getimagesize($name . '.jpg');
        if ($this->currentWight <= $wight && $this->currentHeight <= $height) {
            return $currentImage;
        } else {
            $coeffOfOriginalImage = $this->aspectRatio();
            if ($this->currentWight >= $this->currentHeight) {
                return $this->resizeWideImage($currentImage, $wight, $height, $coeffOfOriginalImage);
            } else{
                return $this->resizeExtendedImage($currentImage, $wight, $height, $coeffOfOriginalImage);
            }
        }
    }


    private function resizeWideImage($currentImage, $wight, $height, $coeffOfOriginalImage)
    {
        $posX = ($height * $coeffOfOriginalImage - $wight) / 2;
        $tempImage = imagecreatetruecolor(
            round($height * $coeffOfOriginalImage),
            $height);
        imagecopyresized(
            $tempImage,
            $currentImage,
            0,
            0,
            0,
            0,
            round($height * $coeffOfOriginalImage),
            $height,
            $this->currentWight,
            $this->currentHeight);
        return imagecrop($tempImage, ['x' => $posX, 'y' => 0, 'width' => $wight, 'height' => $height]);
    }

    private function resizeExtendedImage($currentImage, $wight, $height, $coeffOfOriginalImage)
    {
        $posY = (($height / $coeffOfOriginalImage) - $height) / 2;
        $tempImage = imagecreatetruecolor(
            $wight,
            round($wight / $coeffOfOriginalImage));
        imagecopyresized(
            $tempImage,
            $currentImage,
            0,
            0,
            0,
            0,
            $wight,
            round($wight / $coeffOfOriginalImage),
            $this->currentWight,
            $this->currentHeight);
        return imagecrop($tempImage, ['x' => 0, 'y' => $posY, 'width' => $wight, 'height' => $height]);
    }

    private function aspectRatio()
    {
        return round($this->currentWight / $this->currentHeight);
    }
}