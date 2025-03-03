<?php
// src/Controller/StatistiqueController.php
namespace App\Controller;

use App\Service\DompdfService;
use App\Service\PostStatisticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


class StatistiqueController extends AbstractController
{
    #[Route('/admin/post-statistics', name: 'admin_post_statistics')]
    public function index(PostStatisticsService $postStatisticsService): Response
    {
        // Get statistics
        $mostFrequentWords = $postStatisticsService->getMostFrequentWords(10); // Top 10 words
        $mostActiveDays = $postStatisticsService->getMostActiveDays(7); // Top 7 days
        $postsPerUser = $postStatisticsService->getPostsPerUser(5); // Top 5 users

        return $this->render('statistics/post_statistics.html.twig', [
            'mostFrequentWords' => $mostFrequentWords,
            'mostActiveDays' => $mostActiveDays,
            'postsPerUser' => $postsPerUser,
        ]);
    }

    #[Route('/admin/post-statistics/pdf', name: 'admin_post_statistics_pdf', methods: ['POST'])]
    public function generatePdf(Request $request, DompdfService $dompdfService): Response
    {
        // Get the HTML content from the request
        $data = json_decode($request->getContent(), true);
        $html = $data['html'];

        // Generate the PDF
        $pdf = $dompdfService->generatePdf($html, 'post_statistics.pdf');

        // Return the PDF as a response
        return new Response(
            $pdf,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="post_statistics.pdf"',
            ]
        );
    }
}