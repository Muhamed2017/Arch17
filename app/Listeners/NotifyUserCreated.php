<?php

namespace App\Listeners;

use App\Events\UserCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
// use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Events\UserCreted;
class NotifyUserCreated
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserCreated  $event
     * @return void
     */
    public function handle(UserCreated $event)
    {

        $users = User::all();

        foreach($users as $user) {
          \Mail::send('welcome', ['user' => $event->user], function ($m) use ($user) {
            $user_fullName = $user->first_name.' '.$user->last_name ;
            $m->to($user->email, $user_fullName);
            $m->subject('Welcome New Archian17');
        });
        }


    }
}
