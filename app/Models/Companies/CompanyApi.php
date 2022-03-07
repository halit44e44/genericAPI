<?php

namespace App\Models\Companies;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyApi extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $connection= 'mysql';
    protected $table = 'companies_apis';
    protected $fillable = [
         'authorization',
         'api_name',
         'api_key',
         'feedback_url',
         'client_id',
         'client_key',
        //  'status',
      ];
}