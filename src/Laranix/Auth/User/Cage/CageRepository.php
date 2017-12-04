<?php
namespace Laranix\Auth\User\Cage;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class CageRepository implements Repository
{
    /**
     * Get cage by ID
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model|\Laranix\Auth\User\Cage\Cage|null
     */
    public function getById(int $id): ?Model
    {
        return $this->getModel()->newQuery()->find($id);
    }

    /**
     * Get cages for user
     *
     * @param int  $id
     * @param int  $scopes
     * @param int  $limit
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByUser(int $id, int $scopes = self::DEFAULT, int $limit = 15): LengthAwarePaginator
    {
        $query = $this->getModel()->newQuery()->where('user_id', $id);

        return $this->getScopes($query, $scopes)->paginate($limit);
    }

    /**
     * Get cages issued by user
     *
     * @param int  $id
     * @param int  $scopes
     * @param int  $limit
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByIssuer(int $id, int $scopes = self::DEFAULT, int $limit = 15): LengthAwarePaginator
    {
        $query = $this->getModel()->newQuery()->where('issuer_id', $id);

        return $this->getScopes($query, $scopes)->paginate($limit);
    }

    /**
     * Get cages for area
     *
     * @param string $area
     * @param int    $scopes
     * @param int    $limit
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByArea(string $area, int $scopes = self::DEFAULT, int $limit = 15): LengthAwarePaginator
    {
        $query = $this->getModel()->newQuery()->where('area', $area);

        return $this->getScopes($query, $scopes)->paginate($limit);
    }

    /**
     * Get cages by attributes
     *
     * @param array $attributes
     * @param int   $scopes
     * @param int   $limit
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByAttributes(array $attributes, int $scopes = self::DEFAULT, int $limit = 15) : LengthAwarePaginator
    {
        if (empty($attributes)) {
            return null;
        }

        $query = $this->getModel()->newQuery();

        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }

        return $this->getScopes($query, $scopes)->paginate($limit);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Laranix\Auth\User\Cage\Cage
     */
    public function getModel(): Model
    {
        return new Cage();
    }

    /**
     * Get scopes
     *
     * @param $query
     * @param $scopes
     * @return mixed
     */
    public function getScopes($query, $scopes)
    {
        if ($scopes & self::DEFAULT) {
            return $query;
        }

        if ($scopes & self::WITH_DELETED) {
            return $query->withTrashed();
        }

        if ($scopes & self::DELETED_ONLY) {
            return $query->onlyTrashed();
        }

        return $query;
    }
}
