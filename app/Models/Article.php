<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Article extends Model
{
    use HasFactory;

    const STATUS_REVIEW = 'Review';
    const STATUS_EVALUATED = 'Evaluated';
    const STATUS_APPROVED = 'Approved';
    const STATUS_REVISION = 'Revision';
    const STATUS_UPDATED = 'Updated';

    protected $fillable = [
        'user_id',
        'title',
        'type_of_report',
        'publication_date',
        'posted_date',
        'time_posted',
        'editor_name',
        'approval_status',
        'url',
        'detailed_summary',
        'analysis',
        'recommendation',
        'image_path',
    ];

    public function comments()
    {
        return $this->hasMany(Comment::class, 'article_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
