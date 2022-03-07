<?php

namespace App\Models\Companies;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyProducts extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $connection= 'mysql';
    protected $table = 'company_products';
}
