<?php
namespace Laranix\Auth\Group;

use Illuminate\Database\Eloquent\Model;

interface Repository
{
    /**
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model|\Laranix\Auth\Group\Group
     */
    public function getById(int $id): ?Model;

    /**
     * @param string $name
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Laranix\Auth\Group\Group
     */
    public function getByName(string $name): ?Model;

    /**
     * Retrieve a group by the given attributes.
     *
     * @param  array $attributes
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Laranix\Auth\Group\Group|null
     */
    public function getByAttributes(array $attributes): ?Model;

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Laranix\Auth\Group\Group
     */
    public function getModel(): Model;
}
