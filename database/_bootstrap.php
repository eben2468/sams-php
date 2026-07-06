<?php

/*
| Minimal bootstrap for CLI database scripts (install / seed).
| Loads the autoloader, helpers and config without starting a session
| or the HTTP router.
*/

require dirname(__DIR__) . '/app/autoload.php';

date_default_timezone_set(config('app.timezone', 'UTC'));
