<?php
namespace Laranix\Auth\User\Groups;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface Repository
{
    /**
     * @param int $userId
     * @param int $groupId
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function getByUserGroup(int $userId, int $groupId): ?Model;

    /**
     * @param int $id
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByGroupId(int $id, int $perPage = 15): LengthAwarePaginator;

    /**
     * @param int $id
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByUserId(int $id, int $perPage = 15): LengthAwarePaginator;

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel(): Model;
}
