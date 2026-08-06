<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithToasts;
use Livewire\Component;

abstract class AdminComponent extends Component
{
    use InteractsWithToasts;
}
