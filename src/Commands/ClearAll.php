<?php

namespace NacAL\Bounce\Commands;

use Illuminate\Console\Command;

class ClearAll extends Command
{
    protected $signature = 'clear:all';

    public function handle()
    {
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('view:clear');
        $this->call('route:clear');
        $this->call('clear-compiled');
        $this->call('config:cache');
    }
}
