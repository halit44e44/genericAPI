<?php

namespace App\Models\Epin;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class EpinProductEntities extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $connection= 'mysql';
    protected $table='epin_product_entities';
    

}
