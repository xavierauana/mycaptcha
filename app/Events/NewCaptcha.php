<?php

namespace App\Events;

class NewCaptcha extends Event
{
    public $captchaString;

    /**
     * Create a new event instance.
     *
     * @param $captchaString
     */
    public function __construct($captchaString)
    {
        $this->captchaString = $captchaString;
    }
}
