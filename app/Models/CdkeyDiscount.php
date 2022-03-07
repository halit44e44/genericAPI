<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class CdkeyDiscount extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $connection= 'mysqlEpin';
    protected $table='cdkey_discount';
    protected $primaryKey = 'product_id';
    public $timestamps = false;
    
    

}
