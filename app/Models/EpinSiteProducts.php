<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EpinSiteProducts extends Model
{
    protected $connection= 'mysqlEpin';
    protected $table='product';
    protected $primaryKey = 'product_id';
    public $timestamps = false;

    
}
