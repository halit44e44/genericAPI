<?php

namespace App\Models\Companies;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyIp extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $connection= 'mysql';
    protected $table = 'companies_ip';
    protected $fillable = [
         'ip',
        //  'status',
      ];
}
