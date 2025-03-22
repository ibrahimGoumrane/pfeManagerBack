<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function bookmarks()
    {
        return $this->belongsToMany(User::class, 'bookmarks');
    }
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'report_tags');
    }


}
