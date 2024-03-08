<div
    x-data="{
    height:0,
    conversationElement:document.getElementById('conversation'),
    markAsRead:null
        }"
    x-init="
        height= conversationElement.scrollHeight;
        $nextTick(()=>conversationElement.scrollTop= height);


        Echo.private('users.{{Auth()->User()->id}}')
        .notification((notification)=>{
            if(notification['type']== 'App\\Notifications\\MessageRead' && notification['conversation_id']== {{$this->selectedConversation->id}})
            {

                markAsRead=true;
            }

        });
 "

    @scroll-bottom.window="
        $nextTick(()=>conversationElement.scrollTop= conversationElement.scrollHeight);
    "
    class="w-full overflow-hidden ">
    <div class="border-b flex flex-col overflow-y-scroll grow h-full">
                {{-- Header --}}
                <header class="w-full sticky inset-x-0 flex pb-[5px]  pt-[5px]  top-0 z-10 bg-gray-50 border-b">
                <div class="flex w-full items-center px-2 lg:px-4 gap-2 md:gap-5">

                    <a class="shrink-0 lg:hidden" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
                        </svg>
                    </a>

                    {{--   avater--}}
                    <div class="shrink-0">
                        <x-avater class="h-9 w-9 lg:w-11 lg:h-11 "/>

                    </div>

                    <h6 class="font-bold truncate">{{$selectedConversation->getReceiver()->name}}</h6>
                </div>
                </header>

                {{-- body   --}}
                <main
                    @scroll="
                        scropTop= $el.scrollTop;

                        if(scropTop <= 0){
                            window.livewire.emit('loadMore');
                        }
                    "
                    @update-chat-height.window="
                        newHeight = $el.scrollHeight;

                        oldHeight = height;
                        $el.scrollTop = newHeight - oldHeight;

                        height= newHeight;

                    "
                    id="conversation" class="flex flex-col gap-3 p-2.5 overflow-auto flex-grow overscroll-contain overflow-x-hidden w-full my-auto ">
                    @if($loadMessages)
                        @php
                            $previousMessage=null;
                        @endphp
                        @foreach($loadMessages as $key=> $message)

                            {{-- Keep track of the previous message--}}
                        @if($key>0)
                            @php
                                $previousMessage=$loadMessages->get($key-1)
                            @endphp
                        @endif

                    <div
                        wire:key="{{time().$key}}"
                        @class([
                    'max-w-[85%] md:max-w-[78%] flex w-auto gap-2 relative mt-2',
                    'ml-auto'=>$message->sender_id ===auth()->id(),
                             ] )>
                    {{--    avater --}}
                    <div @class(['shrink-0',
                                  'invisible'=>$previousMessage?->sender_id == $message->sender_id,
                                  'hidden'=>$message->sender_id ===auth()->id(),
                            ])>
                    {{-- message body --}}
                        <x-avater/>
                    </div>
                        <div @class(['flex flex-wrap text-[15px] rounded-xl p-2.5 flex flex-col text-black bg-[#f6f6f8fb]',
                              'rounded-bl-none border border-gray-200/40'=>!($message->sender_id ===auth()->id()),
                              'rounded-br-none bg-blue-500/80 text-gray-50'=>$message->sender_id ===auth()->id(),
                              ])>

                            <p class="whitespace-normal truncate text-sm md:text-base tracking-wide lg:tracking-normal">
                               {{$message->body}}
                            </p>

                            <div class="ml-auto flex gap-2">
                                <p @class(['text-xs',
                                            'text-gray-500'=>!($message->sender_id ===auth()->id()),
                                            'text-gray-50'=>$message->sender_id ===auth()->id(),
                                            ])>

                                    {{$message->created_at->format('g:i a')}}
                                </p>
                                {{-- mesage status,only show if message belong auth--}}
                                @if($message->sender_id === auth()->id())

                                    <div  x-data="{markAsRead:@json($message->isRead())}">
                                    {{-- double tickes --}}

                                        <span x-cloak x-show="markAsRead" @class(['text-gray-200'])>
                                             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-all" viewBox="0 0 16 16">
                                                  <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0"/>
                                                  <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708"/>
                                             </svg>

                                        </span>

                                        {{-- single tickes--}}
                                        <span  x-show="!markAsRead" @class(['text-gray-200'])>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                                <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/>
                                            </svg>
                                        </span>

                                </div>

                                @endif

                            </div>
                        </div>

                    </div>
                        @endforeach
                    @endif
                </main>


                {{--  send message--}}
                <footer  class="shrink-0 z-10 bg-gray-50 inset-x-0 ">
        <div class="p-2 border-t">
            <from
                x-data="{body:@entangle('body')}"
{{--                wire:submit.prevent="sendMessage"--}}
{{--               @submit.prevent="$wire.sendMessage"--}}
                method="POST" autocapitalize="off">

                @csrf

                <input type="hidden" autocomplete="false" style="display: none">

                <div class="grid grid-cols-12">

                    <input
                      wire:model.lazy="body"
                        type="text"  autofocus placeholder="Write your Message here"
                         maxlength="1700" class="col-span-10 bg-gray-200 border-0 p-6 outline-0 focus:border-0 h-9 focus:ring-0 hover:ring-0 rounded-lg focus:outline-none">

                    <button x-bind:disabled="!body.trim()" wire:click="sendMessage" class="col-span-2" type="submit">Send</button>

                </div>

            </from>

            @error('body')
                <p>{{$message}}</p>
            @enderror

        </div>


    </footer>

    </div>

</div>
