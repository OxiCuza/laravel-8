<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $blogPost = BlogPost::all();

        if ($blogPost->count() === 0) {
            $this->command->info('You do not have blog post, so no need comments !');
            return;
        }

        $sumComments = $this->command->ask('How many comment would you like to added ?', 50);

        $users = User::all();

        Comment::factory($sumComments)->make()->each(function ($comment) use ($blogPost, $users) {
            $comment->blog_post_id = $blogPost->random()->id;
            $comment->user_id = $users->random()->id;
            $comment->save();
        });
    }
}
