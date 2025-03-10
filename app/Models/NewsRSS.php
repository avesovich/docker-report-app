<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Http;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Feeds;


class NewsRSS extends Model implements Feedable
{
    use HasFactory;

    protected $table = 'news_rss';

    protected $fillable = [
        'title',
        'link',
        'description',
        'pubDate',
        'author',
        'category',
        'image',
        'source',
    ];

    public static function getFeedItems()
    {
        return self::latest('pubDate')->take(50)->get();
    }

    public function toFeedItem(): FeedItem
    {
        return FeedItem::create()
            ->id($this->link ?? url('/'))
            ->title($this->title ?? 'No Title')
            ->summary($this->description ?? 'No Description')
            ->updated(Carbon::parse($this->pubDate ?? now()))
            ->link($this->link ?? url('/'))
            ->authorName($this->author ?? 'Unknown Author');
    }

    public static function fetchAndStoreFeeds()
    {
        $feeds = [
            'BleepingComputer' => 'https://www.bleepingcomputer.com/feed/',
            'HackRead' => 'https://hackread.com/feed/',
            'DarkRead' => 'https://www.darkreading.com/rss.xml',
            'TheHackerNews' => 'https://feeds.feedburner.com/thehackersnews',
        ];
    
        foreach ($feeds as $source => $feedUrl) {
            try {
                $feed = Feeds::make($feedUrl);
    
                foreach ($feed->get_items() as $item) {
                    $image = self::extractImage($item);
    
                    self::updateOrCreate(
                        ['link' => $item->get_link()],
                        [
                            'title'       => $item->get_title(),
                            'description' => strip_tags($item->get_description(), '<p><br>'),
                            'pubDate'     => Carbon::parse($item->get_date())->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
                            'author'      => $item->get_author() ? $item->get_author()->get_name() : 'Unknown Author',
                            'category'    => $item->get_category() ? $item->get_category()->get_label() : 'General',
                            'image'       => $image,
                            'source'      => $source,
                        ]
                    );
                }
            } catch (\Exception) {
                // Silently fail to prevent stopping execution if a feed fails
            }
        }
    }
    
    /**
     * Extracts the best available image from the RSS item.
     */
    private static function extractImage($item)
    {
        // ✅ Extract first image from description
        preg_match('/<img.*?src=["\'](.*?)["\']/', $item->get_description(), $matches);
        $image = $matches[1] ?? null;
    
        // ✅ Use enclosure if available
        if (!$image && $item->get_enclosure()) {
            $image = $item->get_enclosure()->link;
        }
    
        // ✅ Check for media:thumbnail or media:content attributes
        if (!$image) {
            $thumbnail = $item->get_item_tags('http://search.yahoo.com/mrss/', 'thumbnail');
            $image = $thumbnail[0]['attribs']['']['url'] ?? null;
        }
    
        if (!$image) {
            $mediaContent = $item->get_item_tags('http://search.yahoo.com/mrss/', 'content');
            $image = $mediaContent[0]['attribs']['']['url'] ?? null;
        }
    
        // ✅ If no image is found, attempt Open Graph (`og:image`)
        return $image ?: self::fetchOpenGraphImage($item->get_link());
    }
    
    /**
     * Fetches Open Graph image from webpage if RSS feed has no image.
     */
    private static function fetchOpenGraphImage($url)
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36'
            ])->get($url);
    
            if ($response->successful()) {
                preg_match('/<meta property="og:image" content=["\'](.*?)["\']/', $response->body(), $ogMatches);
                return $ogMatches[1] ?? null;
            }
        } catch (\Exception) {
            return null; // Fail silently if request fails
        }
    
        return null;
    }
    
    
}
