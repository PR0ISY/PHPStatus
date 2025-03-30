<?php


namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Psr\Cache\InvalidArgumentException;

class CacheManager
{
    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function get(string $key, callable $callback, int $expiration = 300)
    {
        return $this->cache->get($key, function (ItemInterface $item) use ($callback, $expiration) {
            $item->expiresAfter($expiration);
            return $callback($item);
        });
    }

    public function setCacheGenerationDate(string $cacheKey): \DateTime
    {
        // Récupérer ou créer un élément de cache
        $cacheItem = $this->cache->getItem($cacheKey);

        // Vérifier si l'élément est déjà défini
        if ($cacheItem->isHit() && $cacheItem->get() instanceof \DateTime) {
            return $cacheItem->get();
        }

        // Sinon, créer une nouvelle DateTime
        $newDate = new \DateTime();

        // Définir la valeur dans l'objet de cache
        $cacheItem->set($newDate);

        // Persister dans le cache
        $this->cache->save($cacheItem);

        return $newDate;
    }

    public function getCacheGenerationDate(string $cacheKey): ?\DateTime
    {
        try {
            $cacheValue = $this->cache->get($cacheKey, function () {
                return null;
            });

            if ($cacheValue instanceof \DateTime) {
                return $cacheValue;
            }

            return null;
        } catch (InvalidArgumentException $e) {
            error_log('Invalid cache key: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            error_log('Error retrieving cache generation date for key "' . $cacheKey . '": ' . $e->getMessage());
            return null;
        }
    }
}