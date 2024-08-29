<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;

class CommentController extends Controller
{
    public function create(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'comment' => 'required|string|max:1000',
            'user_id' => 'required|exists:users,id',
            'rating'=>'required|integer|min:1|max:5',
        ]);

        // Create the comment
        Comment::create([
            'comment' => $validatedData['comment'],
            'user_id' => $validatedData['user_id'],
            'rating'=>$validatedData['rating'],
        ]);


        return redirect()->back()->with('success', 'Comment created successfully');

    }
}
