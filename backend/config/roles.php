<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Roles
    |--------------------------------------------------------------------------
    |
    | Define all available roles in the application.
    | Each role can have a name, slug, and description.
    |
    */

    'roles' => [
        'parent' => [
            'name' => 'Parent',
            'slug' => 'parent',
            'description' => 'Parent user with full access to manage children and tasks',
        ],
        'child' => [
            'name' => 'Child',
            'slug' => 'child',
            'description' => 'Child user who can view and complete assigned tasks',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Role
    |--------------------------------------------------------------------------
    |
    | The default role assigned to new users if no role is specified.
    |
    */

    'default' => 'child',
];
