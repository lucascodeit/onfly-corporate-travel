<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use PDO;

class HealthService
{
    public function check(): array
    {
        $status = 'Success';
        $dbVersion = null;

        try {
            $pdo = DB::connection()->getPdo();
            $dbVersion = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
        } catch (\Throwable) {
            $status = 'Error';
        }

        return [
            'laravel_version' => app()->version(),
            'mysql_version' => $dbVersion,
            'database_connection_status' => $status,
        ];
    }
}
