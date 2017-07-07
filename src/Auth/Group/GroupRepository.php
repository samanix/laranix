<?php
namespace Laranix\Auth\Group;

use Illuminate\Database\Eloquent\Model;

class GroupRepository implements Repository
{
    /**
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model|\Laranix\Auth\Group\Group
     */
    public function getById(int $id) : ?Model
    {
        return $this->getModel()->newQuery()->find($id);
    }

    /**
     * @param string $name
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Laranix\Auth\Group\Group
     */
    public function getByName(string $name) : ?Model
    {
        return $this->getModel()
                    ->newQuery()
                    ->where('group_name', $name)
                    ->first();
    }

    /**
     * Retrieve a group by the given attributes.
     *
     * @param  array $attributes
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Laranix\Auth\Group\Group|null
     */
    public function getByAttributes(array $attributes) : ?Model
    {
        if (empty($attributes)) {
            return null;
        }

        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // Eloquent User "model" that will be utilized by the Guard instances.
        $query = $this->getModel()->newQuery();

        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }

        return $query->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Laranix\Auth\Group\Group
     */
    public function getModel() : Model
    {
        return new Group();
    }
}
