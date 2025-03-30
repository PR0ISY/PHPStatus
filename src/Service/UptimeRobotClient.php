<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\ItemInterface;

class UptimeRobotClient
{
    private HttpClientInterface $client;
    private string $apiKey;
    private CacheManager $cacheManager;

    // Ajout de la propriété logs
    private array $logs = [];

    public function __construct(HttpClientInterface $client, CacheManager $cacheManager, string $apiKey)
    {
        $this->client = $client;
        $this->cacheManager = $cacheManager;
        $this->apiKey = $apiKey;
    }

    public function getAllMonitors(): array
    {
        return $this->cacheApiData(
            'urapi_monitors',
            'https://api.uptimerobot.com/v2/getMonitors',
            [
                'api_key' => $this->apiKey,
                'format' => 'json',
                'all_time_uptime_ratio' => 1,
            ],
            'monitors'
        );
    }

    public function getAllIncidents(): array
    {
        return $this->cacheApiData(
            'urapi_incidents',
            'https://api.uptimerobot.com/v2/getMonitors',
            [
                'api_key' => $this->apiKey,
                'format' => 'json',
                'statuses' => '8-9',
                'logs' => '1',
                'logs_limit' => '10',
                'logs_types' => '1-2-98-99'
            ],
            'monitors'
        );
    }

    public function getAllIncidentsWithLogs(): array
    {
        $incidents = [];

        try {
            $incidents = $this->getAllIncidents();

            foreach ($incidents as &$incident) {
                $monitorDetails = $this->fetchMonitorDetails($incident['id']);

                if ($monitorDetails && isset($monitorDetails['monitor']['logs'])) {
                    $incident['logs'] = $monitorDetails['monitor']['logs'];
                    $incident['last_log'] = $incident['logs'][0] ?? null;
                } else {
                    $incident['logs'] = [];
                    $incident['last_log'] = null;
                }
            }
        } catch (\Exception $e) {
            error_log('Error fetching incidents with logs: ' . $e->getMessage());
        }

        return $incidents;
    }

    public function fetchMonitorDetails(string $monitorId): ?array
    {
        $url = sprintf('https://stats.uptimerobot.com/api/getMonitor/iGfJQvQl2S?m=%s', $monitorId);

        try {
            $response = $this->client->request('GET', $url);
            if ($response->getStatusCode() !== 200) {
                return null;
            }

            return $response->toArray();
        } catch (\Exception $e) {
            return null;
        }
    }

    private function cacheApiData(string $cacheKey, string $apiEndpoint, array $body, string $dataKey): array
    {
        return $this->cacheManager->get($cacheKey, function (ItemInterface $item) use ($cacheKey, $apiEndpoint, $body, $dataKey) {
            $item->expiresAfter(300);
            error_log('Fetching data from UptimeRobot API...');

            $response = $this->client->request('POST', $apiEndpoint, ['body' => $body]);
            $data = $response->toArray();

            if (!isset($data[$dataKey]) || empty($data[$dataKey])) {
                throw new \Exception("Unable to retrieve {$dataKey} from monitor data.");
            }

            $this->cacheManager->setCacheGenerationDate($cacheKey . '_date');
            return $data[$dataKey];
        });
    }
}