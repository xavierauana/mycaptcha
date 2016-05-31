<?php
/**
 * Author: Xavier Au
 * Date: 30/5/2016
 * Time: 7:09 PM
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $fillable =[
        'domain'
    ];
}