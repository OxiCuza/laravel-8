<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function commentsOn()
    {
        return $this->morphMany(Comment::class, 'commentable')->descOrder();
    }

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    //    QUERY SCOPE
    public function scopeWithMostBlogPosts(Builder $query)
    {
        return $query->withCount('blogPosts')->orderByDesc('blog_posts_count');
    }

    public function scopeWithMostBlogPostsLastMonth(Builder $query)
    {
        return $query->withCount(['blogPosts' => function (Builder $query) {
            $query->whereBetween(static::CREATED_AT, [now()->subMonths(1), now()]);
        }])
            ->having('blog_posts_count', '>=', 2)
            ->orderByDesc('blog_posts_count');
    }

    public function scopeThatHasCommentedOnPost(Builder $query, BlogPost $post)
    {
        $query->whereHas('comments', function ($query) use ($post) {
            $query->where('commentable_id', $post->id)
                ->where('commentable_type', BlogPost::class);
        });
    }
}
