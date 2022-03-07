<?php

namespace App\Models\Genba;

use App\Models\Platforms;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class GenbaProducts extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $connection= 'mysql';
    protected $fillable = [
        'productID',
        'sku',
        'regionCode',
        'name',
        'isBundle',
    ];

    function productDetails()
    {
        return $this->hasOne(GenbaProductDetails::class,'product_id','id');
    }
    function languages()
    {
        return $this->hasMany(GenbaLanguages::class,'product_id','id');
    }
    function languagesEnglish()
    {
        return $this->hasOne(GenbaLanguages::class,'product_id','id')->where('languageName','English');
    }
    function metaData()
    {
        return $this->hasMany(GenbaMetaData::class,'product_id','id');
    }
    function instractions()
    {
        return $this->hasMany(GenbaInstructions::class,'product_id','id');
    }
    function instractionsEnglish()
    {
        return $this->hasOne(GenbaInstructions::class,'product_id','id')->where('language','English');
    }
    function gameLanguage()
    {
        return $this->hasOne(GenbaGameLanguages::class,'product_id','id');
    }
    function ageRestrictions()
    {
        return $this->hasOne(GenbaAgeRestrictions::class,'product_id','id');
    }
    function images()
    {
        return $this->hasMany(GenbaGraphics::class,'product_id','id');
    }
    function videos()
    {
        return $this->hasMany(GenbaVideoUrls::class,'product_id','id');
    }
    function pricesUsd()
    {
        return $this->hasOne(GenbaPrice::class,'product_id','id')->where('currencyCode','USD');
    }
    function pricesTRY()
    {
        return $this->hasOne(GenbaPrice::class,'product_id','id')->where('currencyCode','TRY');
    }
    

}
