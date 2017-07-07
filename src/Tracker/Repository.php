<?php
namespace Laranix\Tracker;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface Repository
{
    /**
     * Get track by ID
     *
     * @param int $id
     * @return \Laranix\Tracker\Tracker
     */
    public function getById(int $id): ?Tracker;

    /**
     * Get track by user
     *
     * @param int $id
     * @param int $trackableType
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByUser(int $id, int $trackableType = Tracker::TRACKER_ANY, int $perPage = 15) : LengthAwarePaginator;

    /**
     * Get tracks by IP
     *
     * @param string|int $ip
     * @param int        $trackableType
     * @param int        $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByIpv4($ip, int $trackableType = Tracker::TRACKER_ANY, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get tracks by IP range
     *
     * @param string|int $ipMin
     * @param string|int $ipMax
     * @param int        $trackableType
     * @param int        $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByIpv4Range($ipMin, $ipMax = Settings::IP_MAX, int $trackableType = Tracker::TRACKER_ANY, int $perPage = 15): LengthAwarePaginator;

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
    public function getTrack(string $typeName, ?int $typeId = null, int $trackableType = Tracker::TRACKER_ANY, ?int $timeLimit = null, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get tracks by type
     *
     * @param string $typeName
     * @param int    $trackableType
     * @param int    $timeLimit
     * @param int    $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByType(string $typeName, int $trackableType = Tracker::TRACKER_ANY, ?int $timeLimit = null, int $perPage = 15) : LengthAwarePaginator;

    /**
     * Get tracks by flag level
     *
     * @param int $level
     * @param int $trackableType
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByFlagLevel(int $level, int $trackableType = Tracker::TRACKER_ANY, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get tracks by flag range
     *
     * @param int $min
     * @param int $max
     * @param int $trackableType
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByFlagRange(int $min, int $max = Settings::FLAG_MAX, int $trackableType = Tracker::TRACKER_ANY, int $perPage = 15): LengthAwarePaginator;

    /**
     * Delete tracks for given type
     *
     * @param string    $typeName
     * @param int|null  $typeId
     * @param int       $expiryTime Time in seconds to delete for, 0 for all
     * @param int       $trackableType
     * @return int
     */
    public function deleteTracks(string $typeName, ?int $typeId = null, int $expiryTime = 0, int $trackableType = Tracker::TRACKER_LIVE): int;

    /**
     * Trail tracker scope
     *
     * @param $query
     * @return mixed
     */
    public function scopeTrail($query);

    /**
     * Live tracker scope
     *
     * @param $query
     * @return mixed
     */
    public function scopeLive($query);

    /**
     * Get tracker by trackable type
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param int                                $type
     * @return mixed
     */
    public function scopeType($query, int $type = Tracker::TRACKER_ANY);

    /**
     * Get model
     *
     * @return \Laranix\Tracker\Tracker
     */
    public function getModel(): Tracker;
}
