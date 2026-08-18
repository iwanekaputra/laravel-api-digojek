<?php

namespace App\Livewire\Pages;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

use function PHPSTORM_META\type;

class Login extends Component
{

  public $email;
  public $password;


  public function login()
  {
    $this->validate([
      'email' => 'required|min:3',
      'password' => 'required|min:3',
    ]);

    if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
      $user = User::where('email', $this->email)->first();

      if ($user->getRoleNames()->first() == 'korwil') {
        return redirect()->route('korwil.customers.index');
      } else {
        return redirect()->route('dashboard');
      }
    } else {
      $this->dispatch('swal:modal', type: 'error', message: "Email atau Password Salah!");
    }
  }

  public function render()
  {
    return view('livewire.pages.login')->extends('components.layouts.auth');
  }
}
