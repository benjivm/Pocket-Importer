<?php

namespace App;

use Illuminate\Console\Command;

class Authenticate extends Command
{
    protected $signature = 'authenticate';

    protected $description = 'Authenticate with Pocket';

    public function handle(): void
    {
        // @todo: implement authentication
    }
}
