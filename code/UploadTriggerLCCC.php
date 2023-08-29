<?php

namespace App\Console\Commands\FuelTransactions;

use App\Console\Commands\UploadTrigger;

class UploadTriggerLCCC extends UploadTrigger
{
    protected $signature = 'upload-fuel-transactions:lccc';

    protected $description = 'Import fuel transactions LCCC';
}