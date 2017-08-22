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
            'group_name'    => $values->name,
            'group_color'   => $values->color,
            'group_icon'    => $values->icon,
            'group_level'   => $values->level,
            'group_flags'   => json_encode($values->flags),
            'is_hidden'     => $values->hidden,
        ]);
    }
}
