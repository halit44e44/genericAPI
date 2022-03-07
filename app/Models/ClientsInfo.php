<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ClientsInfo extends Model
{
    use  HasFactory;
    protected $connection= 'mysql';
    protected $table='clients_info';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [
    //     'name', 'email',
    // ];

    
}
