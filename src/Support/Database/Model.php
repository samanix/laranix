<?php
namespace Laranix\Support\Database;

use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel
{
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * LaranixBaseModel constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->config = config();
    }

    /**
     * Force fill a model and save
     *
     * @param array $attributes
     * @param array $options
     * @return \Laranix\Support\Database\Model
     */
    protected function fillSave(array $attributes, array $options = []) : Model
    {
        $this->forceFill($attributes)->save($options);

        return $this;
    }

    /**
     * Create new model
     *
     * @param array $attributes
     * @param array $options
     * @return \Laranix\Support\Database\Model
     */
    public function create(array $attributes = [], array $options = []) : Model
    {
        return $this->fillSave($attributes, $options);
    }

    /**
     * Insert or update without dealing with mass assignment
     *
     * @param array         $attributes
     * @param string|array  $key
     * @param array         $options
     * @return \Laranix\Support\Database\Model
     */
    public function updateOrCreateNew(array $attributes, $key, array $options = []) : Model
    {
        $query = $this->newQuery();

        if (is_array($key)) {
            foreach ($key as $column) {
                $query->where($column, $attributes[$column]);
            }
        } else {
            $query->where($key, $attributes[$key]);
        }

        /** @var Model|Model $row */
        $row = $query->first();

        if ($row !== null) {
            $row->updateExisting(array_replace($row->getAttributes(), $attributes));

            return $row;
        }

        return $this->fillSave($attributes, $options);
    }

    /**
     * Update
     *
     * @param array $attributes
     * @param array $options
     * @return bool
     */
    public function updateExisting(array $attributes, array $options = []) : bool
    {
        if (!$this->exists) {
            return false;
        }

        return $this->forceFill($attributes)->save($options);
    }

    /**
     * Create new statically
     *
     * @param array $attributes
     * @param array $options
     * @return \Laranix\Support\Database\Model
     */
    public static function createNew(array $attributes, array $options = []) : Model
    {
        return self::createInstance()->create($attributes, $options);
    }


    /**
     * Create new instance
     *
     * @return \Laranix\Support\Database\Model
     */
    public static function createInstance() : Model
    {
        $modelName = static::class;

        return new $modelName;
    }

    /**
     * Get an attribute from the $attributes array.
     *
     * @param string    $key
     * @param mixed     $default
     * @return mixed
     */
    protected function getAttributeFromArray($key, $default = null)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : $default;
    }
}
