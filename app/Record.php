<?php
/**
 * Author: Xavier Au
 * Date: 30/5/2016
 * Time: 4:55 PM
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $table = "captcha_records";

    protected $fillable = [
        'uuid', 'captcha_string', 'imageUrl', 'status'
    ];
}