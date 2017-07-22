<?php
/**
 * Author: Xavier Au
 * Date: 30/5/2016
 * Time: 1:48 PM
 */

namespace App;


use Intervention\Image\ImageManager;
use Log;

class Captcha
{
    private $number;
    private $width;
    private $height;
    private $captchaImage;
    private $captchaString = "";
    private $imagePath = "";
    private $font_file = "AdventPro-Regular.ttf";
    private $skip_chars = [
        'l',
        "I",
        '1',
        "O",
        "o",
        "0",
        "9",
        "g",
        "q"
    ];

    /**
     * Captcha constructor.
     * @param int    $number
     * @param int    $height
     * @param int    $width
     * @param string $backgroundColor
     */
    public function __construct($number = null, $height = null, $width = null, $backgroundColor = null) {
        $this->height = $height?? 40;
        $this->number = $number?? 5;
        $this->width = $width?? 200;
    }

    public function createImage(array $inputs): Record {
        $this->createImageCanvas($inputs);
        $this->createCaptchaString($inputs);
        $this->createBackgroundNoise($inputs);
        $this->saveCaptchaImage();
        $record = $this->createDBRecord();

        return $record;
    }

    private function createCaptchaString(array $inputs) {
        $fontSize = array_key_exists("fontSize", $inputs) ? $inputs["fontSize"] : ImageSetting::DefaultFontSize;
        $posXConstant = 15;
        $posYConstant = 10;
        $topMargin = ($this->height / 2) + ($fontSize / 2) - $posYConstant;
        $padding = 20;
        $wordSpacing = ($this->width - $padding * 2 + $posXConstant) / $this->number;

        for ($i = 0; $i < $this->number; $i++) {

            do {
                $char = str_random(1);
            } while (in_array($char, $this->skip_chars));


            $this->captchaString .= $char;
            $posX = ($i + 1) * $wordSpacing;

            if ($i > 0) {
                $posX -= $posXConstant;
            }

            $this->captchaImage->text($char, $posX, $topMargin, function ($font) use ($char, $fontSize, $inputs) {
                $numericFontColor = array_key_exists('numericFontColor',
                    $inputs) ? $inputs["numericFontColor"] : ImageSetting::NumericFontColor;
                $alphabetFontColor = array_key_exists('alphabetFontColor',
                    $inputs) ? $inputs["alphabetFontColor"] : ImageSetting::AlphabetFontColor;
                $rotationAngle = array_key_exists('rotationAngle',
                    $inputs) ? intval($inputs["rotationAngle"]) : ImageSetting::TextRotationAngle;

                $fontColor = is_numeric($char) ? $numericFontColor : $alphabetFontColor;
                $font->file(public_path() . '/fonts/' . $this->font_file);
                $font->size($fontSize);
                $font->color($fontColor);
                $font->angle(rand(-$rotationAngle, $rotationAngle));
            });
        }
    }

    private function saveCaptchaImage() {
        $fileName = str_random(16);
        $this->imagePath = public_path() . "/captcha/" . $fileName . ".gif";
        $this->captchaImage->save($this->imagePath);
        $this->imagePath = str_replace(public_path(), "", $this->imagePath);
    }

    private function createBackgroundNoise(array $inputs = []) {
        $color = array_key_exists("noiseColor",
            $inputs) ? $inputs["noiseColor"] : ImageSetting::BackgroundNoiseDefaultColor;
        $this->captchaImage->pixelate(1);
        for ($i = 0; $i < 10; $i++) {
            $x1 = rand(0, $this->width);
            $y1 = 0;
            $x2 = rand(0, $this->width);
            $y2 = $this->height;
            $this->captchaImage->line($x1, $y1, $x2, $y2, function ($draw) use ($color) {
                $draw->color($color);
            });
        }
    }

    private function createImageCanvas(array $inputs = []) {
        $canvasBackgroundColor = array_key_exists("canvasBackgroundColor",
            $inputs) ? $inputs["canvasBackgroundColor"] : ImageSetting::CanvasBackgroundDefaultColor;
        $imageManager = new ImageManager();
        $this->captchaImage = $imageManager->canvas($this->width, $this->height, $canvasBackgroundColor);
    }

    private function createDBRecord() {
        $record = new Record();
        $record->uuid = uniqid("");
        $record->captcha_string = $this->captchaString;
        $record->imageUrl = url($this->imagePath);
        $record->save();

        return $record;
    }

}