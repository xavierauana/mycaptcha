<?php

function public_path(){
    return __DIR__."/public";
}

function bcrypt($value, $options = [])
{
    return app('hash')->make($value, $options);
}