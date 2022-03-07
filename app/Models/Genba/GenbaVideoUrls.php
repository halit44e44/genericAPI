<?php

namespace App\Models\Genba;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class GenbaVideoUrls extends Model
{
    use HasFactory;


    protected $connection= 'mysql';
    protected $table='genba_video_urls';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [
    //     'productID',
    //     'sku',
    //     'regionCode',
    //     'name',
    //     'isBundle',
    // ];

}
