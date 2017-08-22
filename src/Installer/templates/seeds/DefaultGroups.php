<?php

use Laranix\Auth\Group\Settings;
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
        $groups = config('defaultusergroups');
        $insertGroups = [];

        /**
         * @var string $name
         * @var \Laranix\Auth\Group\Settings $group
         */
        foreach ($groups as $name => $group) {
            $settings = new Settings($group);

            if ($settings->hasRequiredSettings()) {
                $insertGroups[] = [
                    'group_name'  => $settings->name,
                    'group_color' => $settings->color,
                    'group_icon'  => $settings->icon,
                    'group_level' => $settings->level,
                    'group_flags' => $settings->flags !== null ? implode(',', $settings->flags) : null,
                    'is_hidden'   => $settings->hidden,
                ];
            }
        }

        app('db')->table(config('laranixauth.groups.table', 'groups'))->insert($insertGroups);
    }
}
