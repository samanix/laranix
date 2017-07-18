<?php
namespace Laranix\Tests\Laranix\Themes\Stubs;

class Themes
{
    public static $themes = [
        'themer' => [
            'themes' => [
                'foo' => [
                    'name'      => 'foo',
                    'path'      => __DIR__ . '/themes/foo',
                    'webPath'   => 'themes/foo',
                    'enabled'   => true,
                    'default'   => true,
                    'override'  => false,
                    'automin'   => false,
                ],
                'bar' => [
                    'name'      => 'bar',
                    'path'      => __DIR__ . '/themes/bar',
                    'webPath'   => 'https://www.bar.com/themes/bar',
                    'enabled'   => true,
                    'default'   => false,
                    'override'  => false,
                    'automin'   => false,
                ],
                'baz' => [
                    'name'      => 'baz',
                    'path'      => __DIR__ . '/themes/baz',
                    'webPath'   => 'https://www.baz.com/themes/baz',
                    'enabled'   => true,
                    'default'   => false,
                    'override'  => false,
                    'automin'   => false,
                ],
            ],

            // Views
            'views' => [
                'style'     => 'style',
                'scripts'   => 'scripts',
            ],

            'cookie' => 'cookie',
            'umask' => 0755,

            'ignored' => [
                'env1',
                'env2',
            ],
        ],
        'app' => [
            'env' => 'testing',
            'url' => 'http://bar.com',
        ],
    ];
}
