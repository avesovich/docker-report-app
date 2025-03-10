<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NewsRSS;

class FetchNewsCommand extends Command
{
    protected $signature = 'fetch:news';
    protected $description = 'Fetch latest news articles from RSS feeds';

    public function handle()
    {
        $this->info('Fetching News RSS Feeds...');
    
        // ✅ Call the static method
        NewsRSS::fetchAndStoreFeeds();
    
        $this->info('News articles updated successfully.');
    }
}
