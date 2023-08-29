<?php

namespace App\Events;

use App\UploadSteps\StepInterface;
use Illuminate\Foundation\Events\Dispatchable;

class NextStepEvent
{
    use Dispatchable;

    public StepInterface $step;

    public function __construct(StepInterface $step)
    {
        $this->step = $step;
    }
}