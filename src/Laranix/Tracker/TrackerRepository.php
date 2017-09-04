<?php
namespace Laranix\Tracker;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TrackerRepository implements Repository
{
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var array
     */
    protected $aliases;

    /**
     * TrackerRepositoryBase constructor.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        $this->aliases = (array) $this->config->get('tracker.aliases', []);
    }

    /**
     * Get track by ID
     *
     * @param int $id
     * @return \Laranix\Tracker\Tracker
     */
    public function getById(int $id) : ?Tracker
    {
        return $this->getModel()->newQuery()->find($id);
    }

    /**
     * Get track by user
     *
     * @param int $id
     * @param int $trackableType
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByUser(int $id, int $trackableType = Tracker::TRACKER_ANY, int $perPage = 15) : LengthAwarePaginator
    {
        return $this->getModel()
                    ->newQuery()
                    ->where('user_id', $id)
                    ->where($this->typeClosure($trackableType))
                    ->paginate($perPage);
    }

    /**
     * Get tracks by IPv4
     *
     * @param string|int $ip
     * @param int        $trackableType
     * @param int        $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByIpv4($ip, int $trackableType = Tracker::TRACKER_ANY, int $perPage = 15) : LengthAwarePaginator
    {
        return $this->getModel()
                    ->newQuery()
                    ->where('ipv4', $this->getLongIp($ip))
                    ->where($this->typeClosure($trackableType))
                    ->paginate($perPage);
    }

    /**
     * Get tracks by IPv4 range
     *
     * @param string|int $ipMin
     * @param string|int $ipMax
     * @param int        $trackableType
     * @param int        $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByIpv4Range($ipMin, $ipMax = Settings::IP_MAX, int $trackableType = Tracker::TRACKER_ANY, int $perPage = 15) : LengthAwarePaginator
    {
        $min = $this->getLongIp($ipMin);
        $max = $this->getLongIp($ipMax);

        return $this->getModel()
                    ->newQuery()
                    ->whereBetween('ipv4', [ $min, $max ])
                    ->where($this->typeClosure($trackableType))
                    ->paginate($perPage);
    }

    /**
     * Get tracks
     *
     * @param string   $typeName
     * @param int|null $typeId
     * @param int      $trackableType
     * @param int      $timeLimit
     * @param int      $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getTrack(string $typeName, ?int $typeId = null, int $trackableType = Tracker::TRACKER_ANY, ?int $timeLimit = null, int $perPage = 15) : LengthAwarePaginator
    {
        $query = $this->getModel()
                      ->newQuery()
                      ->where('tracker_type', $this->getAlias($typeName))
                      ->where('tracker_type_id', $typeId);

        if ($timeLimit !== null) {
            $query->whereRaw("(TIMESTAMPDIFF(MINUTE, created_at, NOW()) <= ?)", $timeLimit);
        }

        return $query->where($this->typeClosure($trackableType))
                     ->paginate($perPage);
    }

    /**
     * Get tracks by type
     *
     * @param string $typeName
     * @param int    $trackableType
     * @param int    $timeLimit
     * @param int    $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getByType(string $typeName, int $trackableType = Tracker::TRACKER_ANY, ?int $timeLimit = null, int $perPage = 15) : LengthAwarePaginator
    {
        $query = $this->getModel()
                      ->newQuery()
                      ->where('tracker_type', $this->getAlias($typeName));

        if ($timeLimit !== null) {
            $query->whereRaw("(TIMESTAMPDIFF(MINUTE, created_at, NOW()) <= ?)", $timeLimit);
        }

        return $query->where($this->typeClosure($trackableType))
                     ->paginate($perPage);
    }

    /**
     * Get tracks by flag level
     *
     * @param int $level
     * @param int $trackableType
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByFlagLevel(int $level, int $trackableType = Tracker::TRACKER_ANY, int $perPage = 15) : LengthAwarePaginator
    {
        return $this->getModel()
                    ->newQuery()
                    ->where('flag_level', $level)
                    ->where($this->typeClosure($trackableType))
                    ->paginate($perPage);
    }

    /**
     * Get tracks by flag range
     *
     * @param int $min
     * @param int $max
     * @param int $trackableType
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByFlagRange(int $min, int $max = Settings::FLAG_MAX, int $trackableType = Tracker::TRACKER_ANY, int $perPage = 15) : LengthAwarePaginator
    {
        $query = $this->getModel()->newQuery();

        if ($max === Settings::FLAG_MAX) {
            $query->where('flag_level', '>=', $min);
        } else {
            $query->whereBetween('flag_level', [$min, $max]);
        }

        $query->where($this->typeClosure($trackableType));

        return $query->paginate($perPage);
    }

    /**
     * Delete tracks for given type
     *
     * @param string    $typeName
     * @param int|null  $typeId
     * @param int       $expiryTime Time in seconds to delete for, 0 for all
     * @param int       $trackableType
     * @return int
     */
    public function deleteTracks(string $typeName, ?int $typeId = null, ?int $expiryTime = 0, int $trackableType = Tracker::TRACKER_LIVE) : int
    {
        $query = $this->getModel()
            ->newQuery()
            ->where('tracker_type', $this->getAlias($typeName));

        if ($typeId !== -1) {
            $query->where('tracker_type_id', $typeId);
        }

        if ($expiryTime !== null && $expiryTime > 0) {
            $query->whereRaw('(TIMESTAMPDIFF(MINUTE, created_at, NOW()) >= ?)', $expiryTime);
        }

        $query->where($this->typeClosure($trackableType));

        return $query->delete();
    }

    /**
     * Check if tracker type has an alias
     *
     * @param string $type
     * @return string
     */
    protected function getAlias(string $type) : string
    {
        return $this->aliases[$type] ?? $type;
    }

    /**
     * Trail tracker scope
     *
     * @param $query
     * @return mixed
     */
    public function scopeTrail($query)
    {
        return $this->scopeType($query, Tracker::TRACKER_TRAIL);
    }

    /**
     * Live tracker scope
     *
     * @param $query
     * @return mixed
     */
    public function scopeLive($query)
    {
        return $this->scopeType($query, Tracker::TRACKER_LIVE);
    }

    /**
     * Get tracker by trackable type
     *
     * @param \Illuminate\Database\Query\Builder    $query
     * @param int $type
     * @return mixed
     */
    public function scopeType($query, int $type = Tracker::TRACKER_ANY)
    {
        if ($type === Tracker::TRACKER_ANY) {
            return null;
        }

        return $query->where('trackable_type', $type);
    }

    /**
     * Return a closure to use in where statement
     *
     * @param $type
     * @return \Closure
     */
    protected function typeClosure(int $type)
    {
        return function ($query) use ($type) {
            $this->scopeType($query, $type);
        };
    }

    /**
     * Get IP as long
     *
     * @param string|int $ip
     * @return int
     */
    protected function getLongIp($ip) : int
    {
        return is_int($ip) ? $ip : ip2long($ip);
    }

    /**
     * Get model
     *
     * @return \Laranix\Tracker\Tracker
     */
    public function getModel() : Tracker
    {
        return new Tracker();
    }
}
