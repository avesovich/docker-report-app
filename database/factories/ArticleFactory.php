<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ArticleFactory extends Factory
{
    protected static $dateCounter;

    public function definition(): array
    {
        if (!isset(self::$dateCounter)) {
            self::$dateCounter = Carbon::create(2025, 2, 9);
        }

        $currentDate = self::$dateCounter->copy();
        self::$dateCounter->addDay();

        return [
            'user_id' => $this->faker->randomElement([2]),
            'publication_date' => $this->faker->date(),
            'posted_date' => now()->toDateString(),
            'time_posted' => now()->toTimeString(),
            'title' => $this->faker->sentence(),
            'type_of_report' => $this->faker->randomElement([
                'Breach', 'Data Leak', 'Malware Information', 'Threat Actors Updates', 
                'Cyber Awareness', 'Vulnerability Exploitation', 'Phishing', 
                'Ransomware', 'Social Engineering', 'Illegal Access'
            ]),
            'url' => $this->faker->url(),
            'approval_status' => $this->faker->randomElement([
                'Review', 'Updated', 'Revision', 'Evaluated', 'Approved'
            ]),
            'editor_name' => $this->faker->randomElement(['Editor1', 'Editor2']),
            'detailed_summary' => $this->faker->paragraphs(2, true),
            'analysis' => $this->faker->paragraph(),
            'recommendation' => $this->faker->paragraph(),
            
            'created_at' => $currentDate,
            'updated_at' => now(),
        ];
    }
}
