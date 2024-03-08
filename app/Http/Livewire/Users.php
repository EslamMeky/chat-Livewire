<?php

namespace App\Http\Livewire;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Users extends Component
{

    public function message($user_id)
    {
       $id=Auth::id();
       $conversation=Conversation::where(function ($query)use($id,$user_id){
           $query->where('sender_id',$id)
               ->where('receiver_id',$user_id);


       })->orWhere(function ($query)use($id,$user_id){
           $query->where('sender_id',$user_id)
               ->where('receiver_id',$id);

       })->first();

       if ($conversation){
           return redirect()->route('chat',['query'=>$conversation->id]);
       }

       #created Conversation
        $createdConversation=Conversation::create([
            'sender_id'=>$id,
            'receiver_id'=>$user_id,
        ]);
        return redirect()->route('chat',['query'=>$createdConversation->id]);


    }
    public function render()
    {
        return view('livewire.users',[
            'users'=>User::where('id','!=',auth()->id())->get()
        ]);
    }
}
