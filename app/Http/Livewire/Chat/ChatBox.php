<?php

namespace App\Http\Livewire\Chat;

use App\Models\Message;
use App\Notifications\MessageRead;
use App\Notifications\MessageSent;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ChatBox extends Component
{
    public $selectedConversation;
    public $body;
    public $loadMessages;
    public $paginate_var = 10;
    protected $listeners=[
      'loadMore'
    ];

    public function getListeners()
    {
        $auth_id=auth()->user()->id;
        return [
            'loadMore',
            "echo-private:users.{$auth_id},.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated"=>'broadcastedNotifications'
        ];
    }

    public function broadcastedNotifications($event)
    {
        if ($event['type']== MessageSent::class){
            if ($event['conversation_id' ]== $this->selectedConversation->id){

                $this->dispatchBrowserEvent('scroll-bottom');


                $newMessages =Message::find($event['message_id']);

                #push messages

                $this->loadMessages->push($newMessages);

                #mark as read
                $newMessages->read_at=now();
                $newMessages->save();

                #broadcast
                $this->selectedConversation->getReceiver()
                    ->notify(new  MessageRead($this->selectedConversation->id) );

            }

        }
    }

    public function loadMore():void
    {
//        dd('detect');
        #increment
        $this->paginate_var += 10;
        #caal LoadMessages
        $this->loadMessages();

        #update the chat height
        $this->dispatchBrowserEvent('update-chat-height');
    }
    public function loadMessages()
    {
        $count=Message::where('conversation_id',$this->selectedConversation->id)->count();

        $this->loadMessages=Message::where('conversation_id',$this->selectedConversation->id)
            ->skip($count - $this->paginate_var)
            ->take($this->paginate_var)
            ->get();


        return $this->loadMessages;

    }
    public function sendMessage()
    {
        $this->validate(['body'=>'required|string']);
        $createMsg=Message::create([
            'conversation_id'=>$this->selectedConversation->id,
            'sender_id'=>auth()->id(),
            'receiver_id'=>$this->selectedConversation->getReceiver()->id,
            'body'=>$this->body,
        ]);
       $this->body='';

       #scroll bottom
        $this->dispatchBrowserEvent('scroll-bottom');

       # push The Message
        $this->loadMessages->push($createMsg);

        #update conversation model
        $this->selectedConversation->updated_at=now();
        $this->selectedConversation->save();

        #refresh chatlist
        $this->emitTo('chat.chat-list','refresh');

        #broadcast
        $this->selectedConversation->getReceiver()
            ->notify(new  MessageSent(
                Auth::User(),
                $createMsg,
                $this->selectedConversation,
                $this->selectedConversation->getReceiver()->id,
            ));

    }

    public function mount(){
      $this->loadMessages();
    }
    public function render()
    {
        return view('livewire.chat.chat-box');
    }
}
