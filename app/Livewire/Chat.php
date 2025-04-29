<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Message;
use Livewire\Component;
use App\Models\ChatRoom;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class Chat extends Component
{
    public $open;
    public $last_message;

    public function mount() {
        $this->open = collect();
    }

    #[On('startChat')]
    public function startChat($id, $name)
    {
        $participants = [$id, Auth::id()];

        $room_id_data = ChatRoom::whereJsonContains('participants', $participants)->first(); 
        if ($room_id_data == null) {
           $room_id_data = ChatRoom::create([
                'participants' => json_encode($participants),
            ]);
        }
        $room_id = $room_id_data->id;
     if(!$this->open->contains('id' , $id) && count($this->open) <= 3){
        $this->open->push([
            'id' => $id,
            'name' => $name,
            'room_id' => $room_id,
        ]); 
        
        

        // $unread_messeges = Message::where('chat_room_id' , $room_id)->where('from', $id)->where('read', false)->get();
        $this->last_message = Message::where('chat_room_id', $room_id)->latest()->limit(50)->get()->reverse();
        $this->dispatch('join_chat', id:$id, name:$name, room_id:$room_id);
    }
}

    #[On('closeChat')]
    public function closeChat($id, $room_id){

        log::info('id' . $id);
         $this->open = $this->open->reject(function($value) use ($id){
            return $value['id'] == $id;
        });
        log::info($this->open);
        // $this->dispatch('leave_chat', room_id:$room_id);
    }
    public function checkIfOpen($friend_id) {

    }
    // #[On('startVoice')]
    public function startVoice($id, $name, $room_id)
    {
        Log::info('From Chat');
        $notify_user = User::find($id);
        $notify_user->notify(new \App\Notifications\CallRequest(Auth::id() ,"voice", Auth::user()->name));
        $this->dispatch('startVoice', $id, $room_id, $name)->to(ConnectionActions::class);
    }

    public function startVideo($id, $name, $room_id)
    {
        $this->dispatch('startVideo', $id, $name, $room_id)->to(ConnectionActions::class);
        $notify_user = User::find($id);
        $notify_user->notify(new \App\Notifications\CallRequest(Auth::id(),"video", Auth::user()->name));
    }
    
    public function render()
    {
        return view('livewire.chat');
    }
}
