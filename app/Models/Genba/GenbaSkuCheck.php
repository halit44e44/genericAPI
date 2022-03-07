<?php

namespace App\Models\Genba;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class GenbaSkuCheck extends Model
{
    use HasFactory;
    protected $connection= 'mysql';
    protected $table='sku_checks';


}
