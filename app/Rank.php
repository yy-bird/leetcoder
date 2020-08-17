<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    //
    protected $fillable = ['contest_id', 'user_id'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function contest()
    {
        return $this->belongsTo('App\Contest');
    }
}
