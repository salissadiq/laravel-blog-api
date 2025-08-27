<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function post(Request $request) {
        $validated = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        if($validated->fails()){
            return response()->json(['errors' => $validated->errors()], 422);
        }
        try {
            $post = new Post();
            $post->title = $request->title;
            $post->content = $request->content;
            $post->user_id = auth()->user()->id;
            $post->save();
            return response()->json(['message' => 'Post created successfully', 'post' => $post], 201);
        } catch (\Exception $exception) {
            return response()->json(['error'=> $exception->getMessage()], 500);
        }
    }
}
