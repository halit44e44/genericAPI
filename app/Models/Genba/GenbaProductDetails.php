<?php

namespace App\Models\Genba;

use App\Models\Platforms;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class GenbaProductDetails extends Model
{
    use HasFactory;

    protected $connection= 'mysql';
    protected $table='genba_product_details';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    function platform()
    {
        return $this->belongsTo(Platforms::class,'platform_id','id');
    }
    function developer()
    {
        return $this->belongsTo(GenbaDevelopers::class,'developer_id','id');
    }
    function publisher()
    {
        return $this->belongsTo(GenbaPublishers::class,'publisher_id','id');
    }

}
