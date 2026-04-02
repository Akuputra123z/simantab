<?php

namespace App\Events;

use App\Models\Lhp;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LhpStatistikUpdated
{
    use Dispatchable, SerializesModels;

    public Lhp $lhp;

    /**
     * Create a new event instance.
     */
    public function __construct(Lhp $lhp)
    {
        $this->lhp = $lhp;
    }
}