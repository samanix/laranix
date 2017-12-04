<?php
namespace Laranix\Auth\Group;

use Illuminate\Database\Eloquent\Model;

trait CreatesGroup
{
    /**
     * @param array|Settings $values
     * @return \Illuminate\Database\Eloquent\Model|\Laranix\Auth\Group\Group
     */
    public function createGroup($values) : Model
    {
        if (is_array($values)) {
            $values = new Settings($values);
        }

        $values->hasRequiredSettings();

        return Group::createNew([
            'name'    => $values->name,
            'color'   => $values->color,
            'icon'    => $values->icon,
            'level'   => $values->level,
            'flags'   => json_encode($values->flags),
            'hidden'  => $values->hidden,
        ]);
    }
}
