<?php
namespace Laranix\Auth\User\Cage;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface Repository
{
    const DEFAULT = 1;
    const WITH_DELETED = 2;
    const DELETED_ONLY = 4;

    /**
     * Get cage by ID
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model|\Laranix\Auth\User\Cage\Cage|null
     */
    public function getById(int $id): ?Model;

    /**
     * Get cages for user
     *
     * @param int  $id
     * @param int  $scopes
     * @param int  $limit
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByUser(int $id, int $scopes = self::DEFAULT, int $limit = 15): LengthAwarePaginator;

    /**
     * Get cages issued by user
     *
     * @param int  $id
     * @param int  $scopes
     * @param int  $limit
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByIssuer(int $id, int $scopes = self::DEFAULT, int $limit = 15): LengthAwarePaginator;

    /**
     * Get cages for area
     *
     * @param string $area
     * @param int    $scopes
     * @param int    $limit
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByArea(string $area, int $scopes = self::DEFAULT, int $limit = 15): LengthAwarePaginator;

    /**
     * Get cages by attributes
     *
     * @param array $attributes
     * @param int   $scopes
     * @param int   $limit
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByAttributes(array $attributes, int $scopes = self::DEFAULT, int $limit = 15): LengthAwarePaginator;

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Laranix\Auth\User\Cage\Cage
     */
    public function getModel(): Model;

    /**
     * Get scopes to apply
     *
     * @param $query
     * @param $scopes
     * @return mixed
     */
    public function getScopes($query, $scopes);
}
