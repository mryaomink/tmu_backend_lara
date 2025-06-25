<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProfile extends Model
{
    //
    protected $fillable = ['user_id', 'phone_number', 'address', 'date_of_birth'];

public function user()
{
    return $this->belongsTo(User::class);
}
}
