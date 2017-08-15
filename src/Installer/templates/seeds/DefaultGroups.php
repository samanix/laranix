<?php

use __UserAppNamespace__Services\Laranix\DefaultUserGroups;
use Illuminate\Database\Seeder;

class DefaultGroups extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $groups = DefaultUserGroups::getGroups();
        $insertGroups = [];

        /**
         * @var string $name
         * @var \Laranix\Auth\Group\Settings $group
         */
        foreach ($groups as $name => $group) {
            $group->hasRequiredSettings();

            $insertGroups[] = [
                'group_name'    => $group->name,
                'group_color'   => $group->color,
                'group_icon'    => $group->icon,
                'group_level'   => $group->level,
                'group_flags'   => $group->flags !== null ? implode(',', $group->flags) : null,
                'is_hidden'     => $group->hidden,
            ];
        }

        app('db')->table(config('laranixauth.groups.table', 'groups'))->insert($insertGroups);
    }
}
