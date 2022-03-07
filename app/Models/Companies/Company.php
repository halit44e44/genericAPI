<?php

namespace App\Models\Companies;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
  use HasFactory;
  protected $connection = 'mysql';
  protected $table = 'companies';

  function companyApi()
  {
    return $this->hasOne(CompanyApi::class, 'company_id', 'id');
  }
  function companyIp()
  {
    return $this->hasMany(CompanyIp::class, 'company_id', 'id');
  }
  function products()
  {
    return $this->hasMany(CompanyProducts::class, 'company_id', 'id')->where('status',1);
  }
}
