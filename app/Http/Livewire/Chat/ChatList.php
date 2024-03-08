<?php

namespace App\Http\Livewire\Chat;

use App\Models\Conversation;
use Livewire\Component;

class ChatList extends Component
{
    public $selectedConversation;
    public $query;

    protected $listeners=['refresh'=>'$refresh'];


    public function deleteByUser($id){
       $user_id=auth()->id();
       $conversation=Conversation::find(decrypt($id));


       $conversation->messages()->each(function ($message) use ($user_id){
           if ($message->sender_id === $user_id){
               $message->update(['sender_deleted_at'=>now()]);
           }
           elseif($message->receiver_id === $user_id){

               $message->update(['receiver_deleted_at'=>now()]);
           }
       });

       $reciverAlsoDeleted= $conversation->messages()
           ->where(function ($query) use ($user_id){

               $query->where('sender_id',$user_id)
               ->orWhere('receiver_id',$user_id);

           })->where(function ($query) use($user_id){
               $query->whereNull('sender_deleted_at')
                   ->orWhereNull('receiver_deleted_at');
           })->doesntExist();

       if ($reciverAlsoDeleted){
           $conversation->forceDelete();
       }

       return redirect(route('chat.index'));

    }
    public function render()
    {
        $user=auth()->user();
        return view('livewire.chat.chat-list',[
            'conversations'=>$user->conversations()->latest('updated_at')->get(),
        ]);
    }
}
