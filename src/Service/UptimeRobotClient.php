<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Psr\Cache\InvalidArgumentException;

class UptimeRobotClient
{
    private HttpClientInterface $client;
    private string $apiKey;
    private CacheInterface $cache;

    public function __construct(HttpClientInterface $client, CacheInterface $cache, string $apiKey)
    {
        $this->client = $client;
        $this->cache = $cache;
        $this->apiKey = $apiKey;
    }

    public function getMonitors(): array
    {
        return $this->cache->get('uptime_monitors', function (ItemInterface $item) {
            $item->expiresAfter(300);
            error_log('Fetching data from UptimeRobot API...');

            $response = $this->client->request('POST', 'https://api.uptimerobot.com/v2/getMonitors', [
                'body' => [
                    'api_key' => $this->apiKey,
                    'format' => 'json',
                    'all_time_uptime_ratio' => 1,
                ],
            ]);

            $data = $response->toArray();

            if (!isset($data['monitors'])) {
                throw new \Exception('Unable to retrieve monitor data.');
            }

            $this->cache->get('cache_generation_date', function (ItemInterface $dateItem) {
                $dateItem->expiresAfter(300);
                return new \DateTime();
            });

            return $data['monitors'];
        });
    }

    public function getCacheGenerationDate(): ?\DateTime
    {
        try {
            return $this->cache->get('cache_generation_date', function () {
                return null;
            });
        } catch (InvalidArgumentException $e) {
            error_log('Invalid cache key: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            error_log('Error retrieving cache generation date: ' . $e->getMessage());
            return null;
        }
    }
}