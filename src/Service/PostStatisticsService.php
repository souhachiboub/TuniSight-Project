<?php
// src/Service/PostStatisticsService.php
namespace App\Service;

use App\Repository\PublicationRepository;

class PostStatisticsService
{
    private $postRepository;

    public function __construct(PublicationRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    /**
     * Get the most frequent words in posts.
     *
     * @param int $limit Number of words to return
     * @return array
     */
    public function getMostFrequentWords(int $limit = 5): array
    {
        $posts = $this->postRepository->findAll();
        if (empty($posts)) {
            return ['labels' => [], 'data' => []];
        }

        $wordCounts = [];
        $stopWords = ['the', 'and', 'is', 'in', 'it', 'to', 'of', 'for', 'on', 'with', 'at', 'by', 'this', 'that', 'are', 'be', 'as', 'was', 'were']; // Common stop words

        foreach ($posts as $post) {
            $words = str_word_count(strtolower($post->getContenu()), 1);
            foreach ($words as $word) {
                if (strlen($word) > 2 && !in_array($word, $stopWords)) { // Ignore short words and stop words
                    $wordCounts[$word] = ($wordCounts[$word] ?? 0) + 1;
                }
            }
        }

        arsort($wordCounts); // Sort by frequency
        $wordCounts = array_slice($wordCounts, 0, $limit, true); // Limit results

        return [
            'labels' => array_keys($wordCounts),
            'data' => array_values($wordCounts),
        ];
    }

    /**
     * Get the days with the most posts.
     *
     * @param int $limit Number of days to return
     * @return array
     */
    public function getMostActiveDays(int $limit = 5): array
    {
        $posts = $this->postRepository->findAll();
        if (empty($posts)) {
            return ['labels' => [], 'data' => []];
        }

        $dayCounts = [];

        foreach ($posts as $post) {
            $date = $post->getDatePublication()->format('Y-m-d');
            $dayCounts[$date] = ($dayCounts[$date] ?? 0) + 1;
        }

        arsort($dayCounts); // Sort by post count
        $dayCounts = array_slice($dayCounts, 0, $limit, true); // Limit results

        return [
            'labels' => array_keys($dayCounts),
            'data' => array_values($dayCounts),
        ];
    }

    /**
     * Get the number of posts per user.
     *
     * @param int $limit Number of users to return
     * @return array
     */
    public function getPostsPerUser(int $limit = 5): array
    {
        $posts = $this->postRepository->findAll();
        if (empty($posts)) {
            return ['labels' => [], 'data' => []];
        }

        $userPostCounts = [];
        $userNames = [];

        foreach ($posts as $post) {
            $user = $post->getUser();
            $userId = $user->getId();
            $userName = $user->getNom(); 

            if (!isset($userPostCounts[$userId])) {
                $userPostCounts[$userId] = 0;
                $userNames[$userId] = $userName;
            }
            $userPostCounts[$userId]++;
        }

        arsort($userPostCounts); // Sort by post count
        $userPostCounts = array_slice($userPostCounts, 0, $limit, true); // Limit results

        // Prepare labels with user names
        $labels = [];
        foreach ($userPostCounts as $userId => $count) {
            $labels[] = $userNames[$userId]; // Use user name as label
        }

        return [
            'labels' => $labels,
            'data' => array_values($userPostCounts),
        ];
    }
}