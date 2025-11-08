<?php

namespace Modules\VendorWebsite\Console\Commands;

use Illuminate\Console\Command;

class VendorWebsiteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:VendorWebsite';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'VendorWebsite Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return Command::SUCCESS;
    }
}
