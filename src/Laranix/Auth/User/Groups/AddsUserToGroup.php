<?php
namespace Laranix\Auth\User\Groups;

use Illuminate\Database\Eloquent\Model;

trait AddsUserToGroup
{
    /**
     * @param array|Settings $values
     * @return \Illuminate\Database\Eloquent\Model|UserGroup
     */
    public function addUserToGroup($values) : Model
    {
        if (is_array($values)) {
            $values = new Settings($values);
        }

        $values->hasRequiredSettings();

        /** @var \Laranix\Auth\User\Groups\UserGroup $usergroup */
        return UserGroup::createNew([
            'user_id'       => $values->user,
            'group_id'      => $values->group,
            'is_primary'    => (int) $values->primary,
            'is_hidden'     => (int) $values->hidden,
        ]);
    }
}
