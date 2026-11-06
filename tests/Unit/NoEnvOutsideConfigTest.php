<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class NoEnvOutsideConfigTest extends TestCase
{
    public function test_env_is_only_read_inside_config_files(): void
    {
        $root = dirname(__DIR__, 2);
        $offenders = [];
        foreach (['app', 'routes', 'bootstrap'] as $dir) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root.'/'.$dir, \FilesystemIterator::SKIP_DOTS));
            foreach ($files as $file) {
                if ($file->getExtension() !== 'php' || str_contains($file->getPathname(), '/bootstrap/cache/')) {
                    continue;
                }
                if (preg_match('/(?<![\w>:])env\(/', file_get_contents($file->getPathname())) === 1) {
                    $offenders[] = substr($file->getPathname(), strlen($root) + 1);
                }
            }
        }

        $this->assertSame([], $offenders, 'env() returns null once config is cached; read values through config() instead');
    }
}
