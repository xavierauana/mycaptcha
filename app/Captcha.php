<?php
/**
 * Author: Xavier Au
 * Date: 30/5/2016
 * Time: 1:48 PM
 */

namespace App;


use App\Events\NewCaptcha;
use Intervention\Image\ImageManager;

class Captcha
{
    private $number;
    private $width;
    private $height;
    private $backgroundColor;
    private $fontSize = 40;
    private $captchaImage;
    private $captchaString="";
    private $imagePath="";

    /**
     * Captcha constructor.
     * @param int    $number
     * @param int    $height
     * @param int    $width
     * @param string $backgroundColor
     */
    public function __construct($number=5, $height=60, $width=200, $backgroundColor="#ffffff")
    {
        $this->height = $height;
        $this->number = $number;
        $this->width = $width;
        $this->backgroundColor = $backgroundColor;
    }

    public function createImage(): Record
    {
        $this->createImageCanvas();
        $this->createCaptchaString();
        $this->createBackgroundNoise();
        $this->saveCaptchaImage();
        $record = $this->createDBRecord();

        return $record;
    }
    
    private function createCaptchaString()
    {
        $posXConstant = 15;
        $posYConstant = 10;
        $topMargin = ($this->height / 2) + ($this->fontSize / 2) - $posYConstant;
        $padding = 20;
        $wordSpacing = ($this->width - $padding * 2 + $posXConstant) / $this->number;
        for ($i = 0; $i < $this->number; $i++) {
            $char = str_random(1);
            $this->captchaString .= $char;
            $posX = ($i + 1) * $wordSpacing;

            if ($i > 0) {
                $posX -= $posXConstant;
            }
            $this->captchaImage->text($char, $posX, $topMargin, function ($font) use ($char) {
                $fontColor = is_numeric($char) ? "#bebebe" : '#2f4f4f';
                $font->file(public_path() . '/fonts/AdventPro-Regular.ttf');
                $font->size($this->fontSize);
                $font->color($fontColor);
                $font->angle(rand(-60, 60));
            });
        }
    }

    private function saveCaptchaImage()
    {
        $fileName = str_random(16);
        $this->imagePath = public_path() . "/captcha/" . $fileName . ".gif";
        $this->captchaImage->save($this->imagePath);
        $this->imagePath = str_replace(public_path(), "", $this->imagePath);
    }

    private function createBackgroundNoise()
    {
        $this->captchaImage->pixelate(1);
        for ($i = 0; $i < 10; $i++) {
            $x1 = rand(0, $this->width);
            $y1 = 0;
            $x2 = rand(0, $this->width);
            $y2 = $this->height;
            $this->captchaImage->line($x1, $y1, $x2, $y2, function ($draw) {
                $draw->color('#0000ff');
            });
        }
    }

    private function createImageCanvas()
    {
        $imageManager = new ImageManager();
        $this->captchaImage = $imageManager->canvas($this->width, $this->height, $this->backgroundColor);
    }

    private function createDBRecord()
    {
        $record = new Record();
        $record->uuid = uniqid("");
        $record->captcha_string = $this->captchaString;
        $record->imageUrl = url($this->imagePath);
        $record->save();
        return $record;
    }

}