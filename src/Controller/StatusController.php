<?php
namespace App\Controller;

use App\Service\CacheManager;
use App\Service\UptimeRobotClient;
use App\Service\DateFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class StatusController extends AbstractController
{
    #[Route('/', name: 'status_homepage')]
    public function index(
        UptimeRobotClient $uptimeClient,
        CacheManager $cacheManager,
        DateFormatter $dateFormatter,
        SessionInterface $session
    ): Response
    {
        try {
            $monitors = $uptimeClient->getAllMonitors();
            $cacheGenerationDate = $cacheManager->getCacheGenerationDate('urapi_monitors_date');
            $userTimezone = $session->get('user_timezone', 'Europe/Paris');
            $formattedCacheDate = $cacheGenerationDate
                ? $dateFormatter->formatDateForDisplay($cacheGenerationDate, $userTimezone)
                : null;
        } catch (\Exception $e) {
            $this->addFlash('error', 'Data recovery error.');
            error_log('Error in index action: ' . $e->getMessage());
            $monitors = [];
            $formattedCacheDate = null;
        }

        return $this->render('default/index.html.twig', [
            'monitors' => $monitors,
            'cacheGenerationDate' => $formattedCacheDate,
        ]);
    }

    #[Route('/history', name: 'status_history')]
    public function history(
        UptimeRobotClient $uptimeClient,
        CacheManager $cacheManager,
        DateFormatter $dateFormatter,
        SessionInterface $session
    ): Response {
        try {
            $incidents = $uptimeClient->getAllIncidentsWithLogs();

            array_walk($incidents, function (&$incident) {
                if (!isset($incident['logs']) || !is_array($incident['logs'])) {
                    $incident['logs'] = [];
                }
            });

            $cacheGenerationDate = $cacheManager->getCacheGenerationDate('urapi_incidents_date');
            $userTimezone = $session->get('user_timezone', 'Europe/Paris');
            $formattedCacheDate = $cacheGenerationDate
                ? $dateFormatter->formatDateForDisplay($cacheGenerationDate, $userTimezone)
                : null;
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la récupération des incidents.');
            error_log('Erreur dans l\'action history: ' . $e->getMessage());
            $incidents = [];
            $formattedCacheDate = null;
        }

        return $this->render('default/history.html.twig', [
            'incidents' => $incidents,
            'cacheGenerationDate' => $formattedCacheDate,
        ]);
    }
}