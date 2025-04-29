<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class ConnectionActions extends Component
{

    public $call = '';
    public $call_id = '';
    public $call_with = '';
    public $call_type = '';



    #[On('startVoice')]
    public function startVoice($id , $room_id, $name){
        Log::info('Friend Actions');
        $this->call = 'voice';
        $this->call_id = $id;
        $this->call_with = $name;
    }
    
    public function startVideo($id ,$name ,$type ){
        $this->call = 'video';
        $this->call_with = $name;
        $this->call_type = $type;
    }
    #[On('ringing')]
    public function ringing ($id, $name , $type){
        // dd($id, $name , $type);
        $this->call = 'ringing';
        $this->call_with =$name;
        $this->call_id = $id;
        $this->call_type= $type;
        $this->dispatch('startChat', $id , $name)->to(Chat::class);
        // $this->dispatch('ringing', $id, $name, $type);
    }

    public function voiceReady() {
        if ($this->call_type == 'awnser'){
            $this->dispatch('start_call', [
                'constraints' => [
                    'audio' => true,
                    'video' => false
                ],
                'offerer' => true,
            ]);
        }else{
            $this->dispatch('start_call', [
                'constraints' => [
                    'audio' => true,
                    'video' => false
                ],
                'offerer' => false,
            ]);
        }
    }
    public function videoReady() {
        if ($this->call_type == 'awnser'){
            $this->dispatch('start_call', [
                'constraints' => [
                    'audio' => true,
                    'video' => true
                ],
                'offerer' => true,
            ]);
        }else{
            $this->dispatch('start_call', [
                'constraints' => [
                    'audio' => true,
                    'video' => true
                ],
                'offerer' => false,
            ]);
        }
    }
    public function CloseCall() {
        $this->call = '';
        $this->call_with ='';
        $this->call_type= '';
        $this->call_id = '';
        $this->dispatch('close_call');
    }


    public function render()
    {
        return view('livewire.connection-actions');
    }
}
