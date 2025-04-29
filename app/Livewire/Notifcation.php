<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Notifcation extends Component
{
    public $notification;
    public $user_id;
 
    public function mount() {
        $this->user_id = Auth::id();

        $this->notification = collect();
        $notifications = Auth::user()->unreadNotifications()->get();


        if($notifications->count()) {
            foreach($notifications as $notification) {
                $name = User::select('id', 'name')->where('id' , $notification->data['friend_id'])->first();
                $this->notification->push([
                    'name' => $name->name,
                    'event' =>$notification->data['event'],
                    'friend_id' => $notification->data['friend_id'],
                    'id' => $notification->id                
                ]);
            }
        };
    }

    #[On('echo-notification:App.Models.User.{user_id}, notification')]
    public function gotNotification($notification) {
        if ($notification['type'] == 'App\Notifications\FriendRequest'){
            $name = User::select('id', 'name')->where('id' , $notification['friend_id'])->first();
            Log::info('Notificanon sent');
            $this->notification->push([
                'id' => $notification['id'],
                'event' =>$notification['event'],
                'friend_id' => $notification['friend_id'],
                'name' => $name->name,
            ]);
        }
        elseif($notification['type'] == 'App\Notifications\MessageRequest'){
            if($notification['event']  == 'Sent you a message'){
                $friend_id = $notification['friend_id'];
                $this->dispatch('gotMessage', friend_id:$friend_id)->to(UsersList::class);
            }
        } elseif ($notification['type'] == 'App\Notifications\CallRequest') {
            // dd($notification);
            if ($notification['event'] == 'voice') {
                $id = $notification['friend_id'];
                $friend_name = $notification['name'];
                $this->dispatch('ringing', $id, $friend_name, 'voice')->to(ConnectionActions::class);
            } elseif ($notification['event'] == 'video') {
                $id = $notification['friend_id'];
                $friend_name = $notification['name'];
                $this->dispatch('ringing', $id, $friend_name, 'video')->to(ConnectionActions::class);
            }
        }

        // if($notification['event'] == 'accepted')  {
        //     $this->dispatch('acceptFriend', ['friend_id' => $notification->data['friend_id']]);
        // }elseif($notification['event'] == 'removed') {
        //     $this->dispatch('removeFriend', ['friend_id' => $notification->data['friend_id']]);

        // }
    }
    public function render()
    {
        return view('livewire.notifcation');
    }
}
