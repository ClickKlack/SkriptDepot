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
        'active_entitlements' => 'Active entitlements',
        'entitled_since' => 'Entitled since',
        'entitlements_count' => 'Entitlements',
        'install_url' => 'Install link',
        'invitation' => 'Invitation',
        'password_confirmation' => 'Confirm password',
    ],

    'hints' => [
        'invitation' => 'After saving, the user receives an invitation email and sets their own password.',
        'slug' => 'Lowercase letters, digits and hyphens only; part of the delivery URL.',
        'source' => 'Paste the raw userscript; placeholders and watermarks are added automatically on save. Prepared sources containing {{BUILD}} are left unchanged.',
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
        'manage_entitlements' => 'Entitlements',
        'open_install_link' => 'Open install link',
        'copy_install_link' => 'Copy install link',
        'copied' => 'Copied',
        'send_invitation' => 'Send invitation',
        'inject_watermark' => 'Insert watermarks',
        'resend_invitation' => 'Resend invitation',
    ],

    'invitation_status' => [
        'none' => 'Not invited',
        'pending' => 'Invited',
        'accepted' => 'Accepted',
    ],

    'notifications' => [
        'invitation_sent' => 'Invitation sent to :email.',
    ],

    'invitation' => [
        'title' => 'Accept invitation',
        'heading' => 'Welcome to SkriptDepot',
        'subheading' => 'Set your password to activate your account.',
        'submit' => 'Set password',
        'accepted' => 'Your account is now active.',
        'already_accepted' => 'This invitation has already been accepted. Please sign in.',
        'mail' => [
            'subject' => 'Your invitation to SkriptDepot',
            'greeting' => 'Hello :name,',
            'intro' => 'An account has been created for you at SkriptDepot. Use the link below to set your password and confirm your email address.',
            'action' => 'Set password',
            'expiry' => 'The link is valid for :days days. After that an administrator can send you a new invitation.',
            'outro' => 'If you did not expect this invitation, you can ignore this email.',
        ],
    ],

    'password_reset' => [
        'requested_title' => 'Request received',
        'requested_body' => 'If an account with this email address exists, you will shortly receive a link to reset your password.',
    ],

    'entitlements' => [
        'title' => 'Entitlements: :script',
    ],

    'validation' => [
        'version_not_higher' => 'The version must be higher than the current version :latest.',
        'syntax_error' => 'The script is not valid JavaScript (line :line): :message',
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
