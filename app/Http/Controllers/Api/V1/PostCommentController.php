<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\CommentPostedEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreComment;
use App\Http\Resources\Comment;
use App\Models\BlogPost;
use App\Models\Comment as ModelsComment;
use Illuminate\Http\Request;

class PostCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->only(['store']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(BlogPost $post, Request $request)
    {
        $perPage = $request->get('per_page') ?? 10;
        return Comment::collection(
            $post->comments()->with('user')->paginate($perPage)->appends(
                [
                    'per_page' => $perPage
                ]
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BlogPost $post, StoreComment $request)
    {
        $comment = $post->comments()->create([
            'content' => $request->input('content'),
            'user_id' => $request->user()->id
        ]);

        // * TRY TO USE CUSTOM EVENT AND LISTENER FOR SENDING NOTIF / EMAIL
        event(new CommentPostedEvent($comment));

        return new Comment($comment);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(BlogPost $post, ModelsComment $comment)
    {
        return new Comment($comment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(BlogPost $post, ModelsComment $comment, StoreComment $request)
    {
        $comment->content = $request->input('content');
        $comment->save();

        return new Comment($comment);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(BlogPost $post, ModelsComment $comment)
    {
        $comment->delete();

        return response()->noContent();
    }
}
