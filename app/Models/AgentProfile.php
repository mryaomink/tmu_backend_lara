<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentProfile extends Model
{
    //
    protected $fillable = ['user_id', 'agent_code', 'commission_rate', 'company_name'];

public function user()
{
    return $this->belongsTo(User::class);
}

}
