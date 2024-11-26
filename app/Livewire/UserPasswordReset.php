<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserPasswordReset extends Component
{
    public $user_email;
    public $selected_user;
    public $new_password;
    public $new_password_confirm;

    public function rules()
    {
        return [
            'selected_user' => 'required',
            'new_password' => 'required',
            'new_password_confirm' => 'required',
        ];
    }
    public function render()
    {
        return view('livewire.user-password-reset');
    }

    public function resetPassword()
    {
        $this->validate();
        try {
            //code...
            if ($this->new_password == $this->new_password_confirm) {
                $user = User::find($this->selected_user);
                $user->password = bcrypt($this->new_password);
                $user->save();
                session()->flash('success', 'Password has been reset successfully');
                return redirect()->route('user.index');
            }
        } catch (\Throwable $th) {
            //throw $th;
            Log::info($th);
        }
    }
}
