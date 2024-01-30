<?php

return [
    /*
     * Whether or not user avatars should be displayed next to comments.
     */
    'display_avatars' => true,

    /*
     * The icons that are used in the comments component.
     */
    'icons' => [
        'delete' => 'heroicon-s-trash',
        'empty' => 'heroicon-s-chat-bubble-left-right',
    ],

    /*
     * The policy that will be used to authorize actions against comments.
     */
    'model_policy' => \Parallax\FilamentComments\Policies\FilamentCommentPolicy::class,

    /*
     * The number of days after which soft-deleted comments should be deleted.
     *
     * Set to null if no comments should be deleted.
     */
    'prune_after_days' => 30,

    /*
     * The attribute used to display the user's name.
     */
    'user_name_attribute' => 'name',
];
