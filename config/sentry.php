<?php

return array(
    'dsn' => env('MIX_SENTRY_DSN_PUBLIC', 'https://a108795021284fb6a12661a0c5b4044d@sentry.spicy-tees.in/2'),
    'release' => 'dev',
    'send_default_pii' => true
    // capture release as git sha
    // 'release' => trim(exec('git log --pretty="%h" -n1 HEAD')),
);
