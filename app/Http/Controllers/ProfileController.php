<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserUpdateRequest;
use App\Models\Req;
use Illuminate\Http\Request;
use App\Models\User;
use App\Utils\ImageUpload;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Book;

class ProfileController extends Controller
{
    public function index(User $user)
    {
        return view('components.member.dashboard',compact('user'));
    }

    public function update(UserUpdateRequest $request,User $user)
    {
        $user->update($request->validated());
        if ($request->hasFile('image')) {
            $image=ImageUpload::uploadimage($request->image,100,200,'profile/');
            $user->update(['image'=>$image]);
     }


        return redirect()->route('user.profile', $user->username)->with('success', 'User updated successfully');
    }
    public function request(User $user)
    {
        if($user->usertype==0)
        {
            if ($user->request == null) {
                // Create a new request
                $request = new Req();
                $request->user_id = $user->id;
                $request->save();
                return redirect()->route('user.profile', $user->username)->with('success', ' Your Request has been send successfully');

            }

            return redirect()->route('user.profile', $user->username)->with('error', ' Your Request has been send already');
        }
        return redirect()->route('user.profile', $user->username)->with('already', ' You are already an admin');


    }

    public function orders(User $user)
    {
        $orders=$user->orders;
        return view('components.member.ManageBooks',compact('orders'));
    }
    public function cancel(User $user,$orderid)
    {
        $order=Order::findOrFail($orderid);
        $order->update([
            'status'=>'Canceled'
        ]);
        $orderDetails = OrderDetails::where('order_id', $orderid)->get();

        foreach ($orderDetails as $detail) {
            $book = Book::find($detail->book_id);
            if ($book) {
                // Adjust the stock
                $book->copies_owned += $detail->quantity;
                $book->save();
            }
        }


        return redirect()->route('user.orders',['user'=>$user]);
    }
    public function delete(User $user,$orderid)
    {
        $order=Order::findOrFail($orderid);
        $order->delete();
        return redirect()->route('user.orders',['user'=>$user]);
    }


}
