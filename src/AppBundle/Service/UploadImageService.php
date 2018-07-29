<?php

namespace AppBundle\Service;


class UploadImageService
{
    const WIGHT = 1000;
    const HEIGHT = 1000;

    private $currentImageWight;
    private $currentImageHeight;
    /**
     * @var string $uuid
     */
    private $uuid;

    /**
     * UploadImageService constructor.
     */
    public function __construct()
    {
        $this->uuid = uniqid();
    }

    /**
     * @param $image
     */
    private function saveImage($image)
    {
        imagejpeg($image, $this->uuid . '.jpg');
    }

    /**
     * @param $image
     * @return array
     * @throws \Exception
     */
    public function handleIncomeImage($image)
    {
        if (!getimagesize($image)) {
            throw new \Exception('This file does not supported');
        }
        $this->resizeIncomeImage($image);
        return ['uuid' => $this->uuid];
    }

    private function resizeIncomeImage($image)
    {
        list($this->currentImageWight, $this->currentImageHeight) = getimagesize($image);
        $image = imagecreatefromjpeg($image);
        if (
            $this->currentImageWight <= self::WIGHT &&
            $this->currentImageHeight <= self::HEIGHT
        ) {
            $finalImage = imagecrop($image, [
                'x' => 0,
                'y' => 0,
                'width' => $this->currentImageWight,
                'height' => $this->currentImageHeight
            ]);
        } else {
            $coeff = $this->aspectRatio();
            if ($this->isImageWide()) {
                $finalImage = imagecreatetruecolor(self::WIGHT, self::WIGHT / $coeff);
                imagecopyresized(
                    $finalImage,
                    $image,
                    0,
                    0,
                    0,
                    0,
                    self::WIGHT,
                    self::WIGHT / $coeff,
                    $this->currentImageWight,
                    $this->currentImageHeight);
            } else {
                $finalImage = imagecreatetruecolor(self::WIGHT * $coeff, self::HEIGHT);
                imagecopyresized(
                    $finalImage,
                    $image,
                    0,
                    0,
                    0,
                    0,
                    self::WIGHT * $coeff,
                    self::HEIGHT,
                    $this->currentImageWight,
                    $this->currentImageHeight);
            }
        }
        $this->saveImage($finalImage);
    }

    private function isImageWide(): bool
    {
        return ($this->currentImageWight >= $this->currentImageHeight);
    }

    private function aspectRatio()
    {
        return $this->currentImageWight / $this->currentImageHeight;
    }

}