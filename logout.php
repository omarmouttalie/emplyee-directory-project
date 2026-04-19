<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

logoutAdmin();
redirect('login.php?status=logged_out');
