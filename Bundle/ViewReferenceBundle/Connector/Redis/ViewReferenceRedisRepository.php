<?php

namespace Victoire\Bundle\ViewReferenceBundle\Connector\Redis;

use Predis\ClientInterface;
use Victoire\Bundle\ViewReferenceBundle\Connector\ViewReferenceConnectorRepositoryInterface;

/**
 * Class ViewReferenceRedisRepository.
 */
class ViewReferenceRedisRepository implements ViewReferenceConnectorRepositoryInterface
{
    protected $redis;
    private $alias = 'reference';
    private $tools;

    /**
     * ViewReferenceRedisRepository constructor.
     *
     * @param ClientInterface $redis
     */
    public function __construct(ClientInterface $redis)
    {
        $this->redis = $redis;
        $this->tools = new ViewReferenceRedisTool();
    }

    /**
     * This method return all references.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->redis->keys($this->alias.'*');
    }

    /**
     * This method return all references matching with criteria
     * Support "AND" and "OR".
     *
     * @param array  $criteria
     * @param string $type
     *
     * @return array
     */
    public function getAllBy(array $criteria, $type = 'AND')
    {
        $filters = [];
        $criteria = $this->tools->redislizeArray($criteria);
        // Create hashs for every index needed
        foreach ($criteria as $key => $value) {
            $filters[] = $this->tools->generateKey($key.'_'.$this->alias, $value);
        }
        // Call the right method for "AND" or "OR"
        if ($type != 'OR') {
            return $this->redis->sinter($filters);
        } else {
            return $this->redis->sunion($filters);
        }
    }

    /**
     * This method return an array of references matching with an array of id.
     *
     * @param array $data
     *
     * @return array
     */
    public function getResults(array $data)
    {
        $results = [];
        foreach ($data as $item) {
            $results[] = $this->findById($item);
        }

        return $results;
    }

    /**
     * This method return a reference matching with a ref id.
     *
     * @param $id
     *
     * @return array
     */
    public function findById($id)
    {
        return $this->tools->unredislizeArray($this->redis->hgetall($this->alias.':'.$id));
    }

    /**
     * This method return a specific value for an id or null if not exist.
     *
     * @param $value
     * @param $id
     *
     * @return mixed|null|string
     */
    public function findValueForId($value, $id)
    {
        $reference = $this->findById($id);
        if (!isset($reference[$value])) {
            return;
        }

        return $this->tools->unredislize($reference[$value]);
    }

    /**
     * getChildren ids for a ref id.
     *
     * @param $id
     *
     * @return array
     */
    public function getChildren($id)
    {
        return $this->redis->smembers($this->alias.':'.$id.':children');
    }

    /**
     * @param $alias
     *
     * @return $this
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Find a ref id for an url.
     *
     * @param string $url
     * @param string $locale
     *
     * @return mixed|string
     */
    public function findRefIdByUrl($url = '', $locale = 'fr')
    {
        if ($url == '' || $url[0] != '/') {
            $url = '/'.$url;
        }
        $refId = $this->tools->unredislize($this->redis->get($locale.':'.$url));

        return $refId;
    }
}