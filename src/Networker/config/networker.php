<?php

return [
   /*
    * Ensure all keys are unique in slugs and custom links,
    * as otherwise they will overwrite each other when
    * they are parsed.
    */

   /*
    * Known hosts (and thus only require the slug) are:
    *
    * Facebook, Twitter, Instagram, BitBucket, GitHub, Reddit
    */
    'slugs' => [
        'facebook'  => 'samanixcom',
        'twitter'   => 'samanixcom',
        'instagram' => 'samanix',
        'bitbucket' => 'samanix',
        'github'    => 'samanix',
        'reddit'    => 'samanix', // /r/ is not required
    ],

   /*
    * Add custom links like so:
    *
    * 'key'    => [
    *      'url' => 'Link to site',
    *      'slug' => 'link slug to your page',
    * ]
    *
    * Ensure you include the protocol as well in the url, or you may have unexpected results
    *
    * If either url or slug is omitted, the link is skipped, include even if empty
    */
    'links' => [
        'linkedin' => [
            'url'   => 'https://linkedin.com',
            'slug'  => 'in/samanix',
        ],
    ],
];
