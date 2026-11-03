<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PnmttProductionEnvironmentTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (! defined('PNMTC_AUTH_ENV_TEST')) {
            define('PNMTC_AUTH_ENV_TEST', true);
        }
        require_once dirname(__DIR__, 2).'/.github/scripts/configure-pnmtc-authcentral.php';
    }

    public function test_updates_all_duplicate_keys_without_changing_unrelated_settings(): void
    {
        $old = "APP_NAME=College\r\nAUTH_METHOD=regular\r\n# keep this comment\r\nAUTH_METHOD=regular\r\nDB_PASSWORD=private-test-value\r\n";
        $updated = pnmtcEnvironmentValues($old, ['AUTH_METHOD' => 'authcentral', 'AUTHCENTRAL_SERVICE_KEY' => str_repeat('k', 64)]);
        $this->assertSame(1, substr_count($updated, 'AUTH_METHOD='));
        $this->assertStringContainsString('AUTH_METHOD=authcentral', $updated);
        $this->assertStringContainsString('DB_PASSWORD=private-test-value', $updated);
        $this->assertStringContainsString('# keep this comment', $updated);
    }

    public function test_rejects_environment_line_injection(): void
    {
        $this->expectException(\RuntimeException::class);
        pnmtcEnvironmentValues('', ['AUTH_METHOD' => "authcentral\nOTHER=value"]);
    }

    public function test_atomic_replacement_preserves_private_mode_and_contents(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'pnmtc-env-test-');
        chmod($path, 0600);
        file_put_contents($path, "APP_NAME=Test\n");
        try {
            pnmtcAtomicEnvironmentWrite($path, "APP_NAME=Updated\n");
            clearstatcache(true, $path);
            $this->assertSame("APP_NAME=Updated\n", file_get_contents($path));
            $this->assertSame(0600, fileperms($path) & 0777);
        } finally {
            unlink($path);
        }
    }
}
