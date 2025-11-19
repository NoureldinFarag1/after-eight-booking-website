<?php

// Config for Google reCAPTCHA (anhskohbo/no-captcha)
// Accept both standard keys (NOCAPTCHA_*) and legacy/custom names (CAPTCHA_*)
return [
    'secret' => env('NOCAPTCHA_SECRET', env('CAPTCHA_SECRET_KEY')),
    'sitekey' => env('NOCAPTCHA_SITEKEY', env('CAPTCHA_SITE_KEY')),
    'options' => [
        'timeout' => 30,
    ],
];
