<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Question;
use App\Submission;

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

    public function getQuestionsAttribute(){
        return Question::where('contest_id', $this->contest_id)->get();
    }

    public function getSubmissionsAttribute(){
        return Submission::where('contest_id', $this->contest_id)->where('user_id', $this->user_id)->get();
    }

    protected $appends = ['questions', 'submissions'];
}
