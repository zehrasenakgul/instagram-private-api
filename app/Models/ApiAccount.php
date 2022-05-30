<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiAccount extends Model
{
    protected $table = "api_accounts";
    protected $fillable = ["userId", "userName", "engagementRate"];
    use HasFactory;
}
