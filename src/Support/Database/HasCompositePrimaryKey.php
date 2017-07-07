<?php
namespace Laranix\Support\Database;

use Illuminate\Database\Eloquent\Builder;

trait HasCompositePrimaryKey
{
    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        $keys = $this->getKeyName();

        if (!is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $key) {
            $query->where($key, '=', $this->getKeyForSaveQuery($key));
        }

        return $query;
    }

    /**
     * Get the primary key value for a save query.
     *
     * @param string $key
     * @return mixed
     */
    protected function getKeyForSaveQuery($key = null)
    {
        if ($key === null) {
            $key = $this->getKeyName();
        }

        return isset($this->original[$key]) ? $this->original[$key] : $this->getAttribute($key);
    }
}
