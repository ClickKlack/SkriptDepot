<?php

return [
    'brand' => 'SkriptDepot',

    'nav' => [
        'administration' => 'Administration',
        'scripts' => 'Scripts',
        'forensics' => 'Forensics',
    ],

    'resources' => [
        'user' => ['label' => 'User', 'plural' => 'Users'],
        'script' => ['label' => 'Script', 'plural' => 'Scripts'],
        'script_version' => ['label' => 'Script version', 'plural' => 'Script versions'],
        'entitlement' => ['label' => 'Entitlement', 'plural' => 'Entitlements'],
        'delivery' => ['label' => 'Delivery', 'plural' => 'Deliveries'],
    ],

    'fields' => [
        'name' => 'Name',
        'email' => 'Email address',
        'password' => 'Password',
        'is_admin' => 'Administrator',
        'locale' => 'Language',
        'slug' => 'Slug',
        'description' => 'Description',
        'version' => 'Version',
        'source' => 'Master source',
        'notes' => 'Notes',
        'script' => 'Script',
        'user' => 'User',
        'token' => 'Token',
        'is_active' => 'Active',
        'type' => 'Type',
        'ip' => 'IP address',
        'user_agent' => 'User agent',
        'created_at' => 'Created at',
        'build_hash' => 'Build hash',
        'latest_version' => 'Current version',
        'versions_count' => 'Versions',
        'entitlements_count' => 'Entitlements',
        'install_url' => 'Install link',
    ],

    'hints' => [
        'password_edit' => 'Leave empty to keep the current password.',
        'slug' => 'Lowercase letters, digits and hyphens only; part of the delivery URL.',
        'source' => 'Must contain the placeholders {{VERSION}}, {{BASE}}, {{TOKEN}}, {{SLUG}} and {{BUILD}} at least twice.',
        'version_immutable' => 'Source and version number cannot be changed after creation. Create a new, higher version instead.',
        'entitlement_immutable' => 'User and script are fixed after creation. Revoke access via "Active".',
    ],

    'locales' => [
        'de' => 'Deutsch',
        'en' => 'English',
    ],

    'delivery_types' => [
        'meta' => 'Metadata',
        'user' => 'Script',
    ],

    'actions' => [
        'open_install_link' => 'Open install link',
        'copy_install_link' => 'Copy install link',
        'copied' => 'Copied',
    ],

    'validation' => [
        'version_not_higher' => 'The version must be higher than the current version :latest.',
        'entitlement_exists' => 'This user is already entitled to this script.',
        'master_script' => [
            'header' => 'The source contains no // ==UserScript== … // ==/UserScript== block.',
            'version' => 'The header must contain "@version {{VERSION}}".',
            'update_url' => 'The header must contain "@updateURL {{BASE}}/s/{{TOKEN}}/{{SLUG}}.meta.js".',
            'download_url' => 'The header must contain "@downloadURL {{BASE}}/s/{{TOKEN}}/{{SLUG}}.user.js".',
            'build' => 'The placeholder {{BUILD}} must appear at least twice.',
        ],
    ],

    'resolve' => [
        'title' => 'Resolve watermark',
        'navigation' => 'Resolve watermark',
        'input_label' => 'Build hash, token or complete script text',
        'input_help' => 'Enter an 8-character build hash or paste the full contents of a leaked script file.',
        'submit' => 'Resolve',
        'results' => 'Result',
        'no_match' => 'No match. Neither a known build hash nor a token was found.',
        'identifier' => 'Identifier',
        'method' => 'Method',
        'methods' => [
            'token' => 'Token from update URL',
            'watermark' => 'Watermark table',
            'computed' => 'Recomputed',
        ],
        'active' => 'Entitlement active',
        'inactive' => 'Entitlement revoked',
    ],

    'portal' => [
        'my_scripts' => 'My scripts',
        'install' => 'Install',
        'version' => 'Version :version',
        'no_version' => 'No version published yet.',
        'empty' => 'No scripts are currently enabled for you.',
        'hint_title' => 'Note on sharing',
        'hint' => 'Every copy is tied to your account and carries an individual watermark. Passing it on to third parties is traceable.',
    ],
];
