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
    #[Route('/status', name: 'status_homepage')]
    public function status(
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

    #[Route('/incidents', name: 'status_incidents')]
    public function incidents(
        UptimeRobotClient $uptimeClient,
        CacheManager $cacheManager,
        DateFormatter $dateFormatter,
        SessionInterface $session
    ): Response {
        $incidents = [];
        try {
            $incidents = $uptimeClient->getAllIncidentsWithLogs();

            if (empty($incidents)) {
                $this->addFlash('notice', 'No incidents reported.');
            }

            $cacheGenerationDate = $cacheManager->getCacheGenerationDate('urapi_incidents_date');
            $userTimezone = $session->get('user_timezone', 'Europe/Paris');
            $formattedCacheDate = $cacheGenerationDate
                ? $dateFormatter->formatDateForDisplay($cacheGenerationDate, $userTimezone)
                : null;
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error retrieving data from the API.');
            error_log('Error in action history: ' . $e->getMessage());
            $formattedCacheDate = null;
        }

        return $this->render('default/incidents.html.twig', [
            'incidents' => $incidents,
            'cacheGenerationDate' => $formattedCacheDate,
        ]);
    }
}