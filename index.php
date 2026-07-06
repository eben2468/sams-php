<?php

/*
|--------------------------------------------------------------------------
| SAMS — Root entry point
|--------------------------------------------------------------------------
| The real front controller lives in public/index.php. When mod_rewrite is
| available the root .htaccess forwards requests into public/ automatically;
| this stub is the fallback for setups where rewriting is disabled or the
| project folder is opened directly (e.g. http://localhost/sams-php/).
|
| public/index.php resolves its paths from its own __DIR__, so including it
| here boots the application exactly as if it had been hit directly.
*/

require __DIR__ . '/public/index.php';
