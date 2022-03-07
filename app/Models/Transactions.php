<?php

namespace App\Models;

use App\Models\Epin\EpinProductEntities;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transactions extends Model
{
    use  HasFactory;
    use SoftDeletes;

    protected $connection= 'mysql';
    protected $table='transactions';
    

    public function clientInfo()
    {
        return $this->hasOne(ClientsInfo::class,'id','client_info_id');
    }
    public function productEntity()
    {
        return $this->hasOne(EpinProductEntities::class,'id','epinProduct_entity_id');
    }

    
}
