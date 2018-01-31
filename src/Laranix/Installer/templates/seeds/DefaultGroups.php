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
                    'name'      => $settings->name,
                    'color'     => $settings->color,
                    'icon'      => $settings->icon,
                    'level'     => $settings->level,
                    'flags'     => $settings->flags !== null ? implode(',', $settings->flags) : null,
                    'hidden'    => $settings->hidden,
                ];
            }
        }

        app('db')->table(config('laranixauth.group.table'))->insert($insertGroups);
    }
}
