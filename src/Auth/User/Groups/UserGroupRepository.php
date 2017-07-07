<?php
namespace Laranix\Auth\User\Groups;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class UserGroupRepository implements Repository
{
    /**
     * @param int $userId
     * @param int $groupId
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function getByUserGroup(int $userId, int $groupId) : ?Model
    {
        return $this->getModel()
                    ->newQuery()
                    ->where('user_id', $userId)
                    ->where('group_id', $groupId)
                    ->first();
    }

    /**
     * @param int $id
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByGroupId(int $id, int $perPage = 15) : LengthAwarePaginator
    {
        return $this->getModel()->newQuery()->where('group_id', $id)->paginate($perPage);
    }

    /**
     * @param int $id
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByUserId(int $id, int $perPage = 15) : LengthAwarePaginator
    {
        return $this->getModel()->newQuery()->where('user_id', $id)->paginate($perPage);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel() : Model
    {
        return new UserGroup();
    }
}
