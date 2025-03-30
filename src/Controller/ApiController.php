<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/api/save-user-timezone', name: 'save_user_timezone', methods: ['POST'])]
    public function saveUserTimezone(Request $request, SessionInterface $session): JsonResponse
    {
        $content = json_decode($request->getContent(), true);
        $userTimezone = $content['timezone'] ?? null;

        if (session_status() !== PHP_SESSION_ACTIVE) {
            $session->start();
        }

        if ($userTimezone && in_array($userTimezone, \DateTimeZone::listIdentifiers())) {
            $session->set('user_timezone', $userTimezone);

            return new JsonResponse(['success' => true, 'timezone' => $userTimezone]);
        }

        return new JsonResponse([
            'success' => false,
            'error' => 'Timezone is invalid or not provided.',
        ], 400);
    }
}