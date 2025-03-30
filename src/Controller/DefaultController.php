<?php
namespace App\Controller;

use App\Service\UptimeRobotClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'status_homepage')]
    public function index(UptimeRobotClient $uptimeClient): Response
    {
        try {
            $monitors = $uptimeClient->getMonitors();
            $cacheGenerationDate = $uptimeClient->getCacheGenerationDate();
        } catch (\Exception $e) {
            $this->addFlash('error', 'Data recovery error.');
            error_log('Error in index action: ' . $e->getMessage());
            $monitors = [];
            $cacheGenerationDate = null;
        }

        return $this->render('default/index.html.twig', [
            'monitors' => $monitors,
            'cacheGenerationDate' => $cacheGenerationDate,
        ]);
    }

    #[Route('/history', name: 'status_history')]
    public function history(): Response
    {
        return $this->render('default/history.html.twig');
    }

    #[Route('/debug/monitors', name: 'debug_monitors')]
    public function debug_monitors(UptimeRobotClient $uptimeClient): Response
    {
        try {
            $monitors = $uptimeClient->getMonitors();
        } catch (\Exception $e) {
            $this->addFlash('error', 'Data recovery error.');
            error_log('Error in debug_monitors action: ' . $e->getMessage());
            $monitors = [];
        }

        return $this->render('debug/monitors.html.twig', [
            'monitors' => $monitors
        ]);
    }
}