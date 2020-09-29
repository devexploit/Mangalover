<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Validator;
use App\Models\Comment;
use App\Models\Serie;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->app = Redis::connection();
    }

    public function commentPost(Request $request,$id)
    {
        $rules = [
          'comment' => 'required|min:5'
        ];

        $validator = Validator::make($request->all(),$rules);

        if($validator->fails()){
            return response()->json(['errors'=>$validator->errors()],400);
        }

        $serie = Serie::find($id);

        if(is_null($serie)){
            return response()->json(['error'=>'data not found'],404);
        }

        $userid = $this->app->hget($request->header('Authorization'),'user_id');
        $comment=Comment::create(
            [
                'comment'=>$request->comment,
                'user_id'=>$userid,
                'serie_id'=>$id
            ]
        );
        return response()->json(['data'=>$comment],200);
    }

    public function commentDelete($id)
    {
        $comment = Comment::find($id);
        if(is_null($comment)){
            return response()->json(['error'=>'data not found'],404);
        }

        $comment->delete();
        return response()->json(null,204);

    }
}
