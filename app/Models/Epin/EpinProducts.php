<?php

namespace App\Models\Epin;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class EpinProducts extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $connection= 'mysql';
    protected $table='epin_products';
    
    public function entities()
    {
        return $this->hasMany(EpinProductEntities::class,'epinProduct_id','id')->where('status',1)->whereNull('deleted_at');
    }

}
