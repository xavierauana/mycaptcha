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
    public function __construct($number=null, $height=null, $width=null, $backgroundColor=null)
    {
        $this->height = $height?? 50;
        $this->number = $number?? 60;
        $this->width = $width?? 200;
    }

    public function createImage(array $inputs): Record
    {
        array_key_exists("canvasBackgroundColor", $inputs)? $this->createImageCanvas($inputs["canvasBackgroundColor"]):$this->createImageCanvas();
        $this->createCaptchaString($inputs);
        array_key_exists("noiseColor", $inputs)? $this->createBackgroundNoise($inputs["noiseColor"]):$this->createBackgroundNoise();
        $this->saveCaptchaImage();
        $record = $this->createDBRecord();

        return $record;
    }
    
    private function createCaptchaString(array $inputs)
    {
        $fontSize = array_key_exists("fontSize",$inputs)? $inputs["fontSize"] : ImageSetting::DefaultFontSize;
        $posXConstant = 15;
        $posYConstant = 10;
        $topMargin = ($this->height / 2) + ($fontSize / 2) - $posYConstant;
        $padding = 20;
        $wordSpacing = ($this->width - $padding * 2 + $posXConstant) / $this->number;
        for ($i = 0; $i < $this->number; $i++) {
            $char = str_random(1);
            $this->captchaString .= $char;
            $posX = ($i + 1) * $wordSpacing;

            if ($i > 0) {
                $posX -= $posXConstant;
            }
            $this->captchaImage->text($char, $posX, $topMargin, function ($font) use ($char, $fontSize, $inputs) {
                $numericFontColor = array_key_exists('numericFontColor', $inputs)? $inputs["numericFontColor"] : ImageSetting::NumericFontColor;
                $alphabetFontColor = array_key_exists('alphabetFontColor', $inputs)? $inputs["alphabetFontColor"] : ImageSetting::AlphabetFontColor;
                $rotationAngle = array_key_exists('rotationAngle', $inputs)? intval($inputs["rotationAngle"]): ImageSetting::TextRotationAngle;

                $fontColor = is_numeric($char) ? $numericFontColor : $alphabetFontColor;
                $font->file(public_path() . '/fonts/AdventPro-Regular.ttf');
                $font->size($fontSize);
                $font->color($fontColor);
                $font->angle(rand(-$rotationAngle, $rotationAngle));
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

    private function createBackgroundNoise($color=null)
    {
        $color = $color?? ImageSetting::BackgroundNoiseDefaultColor;
        $this->captchaImage->pixelate(1);
        for ($i = 0; $i < 10; $i++) {
            $x1 = rand(0, $this->width);
            $y1 = 0;
            $x2 = rand(0, $this->width);
            $y2 = $this->height;
            $this->captchaImage->line($x1, $y1, $x2, $y2, function ($draw)use($color) {
                $draw->color($color);
            });
        }
    }

    private function createImageCanvas($canvasBackgroundColor=null)
    {
        $canvasBackgroundColor = $canvasBackgroundColor?? ImageSetting::CanvasBackgroundDefaultColor;
        $imageManager = new ImageManager();
        $this->captchaImage = $imageManager->canvas($this->width, $this->height, $canvasBackgroundColor);
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