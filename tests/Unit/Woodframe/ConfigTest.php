<?php

declare(strict_types=1);

namespace Loom73\Tests\Unit\Woodframe;

use Loom73\Woodframe\Config;
use PHPUnit\Framework\TestCase;

/**
 * Verifies Config's public contract using isolated configuration state.
 *
 * Tests do not load environment files or application-specific configuration.
 *
 * @see Config
 */
final class ConfigTest extends TestCase
{
    protected function setUp(): void
    {
        Config::clear();
    }
    protected function tearDown(): void
    {
        Config::clear();
    }

    /**
     * Vendor MIME types contain dots that must remain part of the array key.
     *
     * Reading and writing an escaped path must preserve existing sibling entries.
     */
    public function testMimeWithDotsIsStoredAsOneKey(): void
    {
        Config::set(
            'uploads.allowed_mimes',
            ['image/jpeg' => 'jpg', ]
        );

        $mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        $path = 'uploads.allowed_mimes.{' . $mime . '}';

        Config::set( $path, 'docx');

        self::assertSame([
            'image/jpeg' => 'jpg',
            $mime => 'docx'
        ], Config::get('uploads.allowed_mimes'));

        self::assertSame('docx', Config::get($path));
    }

    /**
     * Application overrides must preserve defaults that are not explicitly changed.
     */
    public function testPartialOverridePreservesOtherDefaults(): void
    {
        Config::set(
            'uploads',
            [
                'limits' => [
                    'max_size' => 10_485_760,
                    'max_files' => 3,
                ],
                'allowed_mimes' => [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                ]
            ]
        );

        Config::loadOverrides(ROOT_DIR . '/tests/Fixtures/Config/partial');

        self::assertSame(
            5_242_880,
            Config::get('uploads.limits.max_size')
        );

        self::assertSame(
            3,
            Config::get('uploads.limits.max_files')
        );

        self::assertSame([
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ], Config::get('uploads.allowed_mimes'));
    }

    /**
     * An application MIME allowlist must replace the default map completely.
     *
     * MIME types omitted by the override must no longer be allowed.
     */
    public function testMimeOverrideReplacesTheDefaultMap(): void
    {
        Config::set('uploads.allowed_mimes', [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ]);

        Config::loadOverrides(
            ROOT_DIR . '/tests/Fixtures/Config/replace-list',
        );
        self::assertSame([
            'image/jpeg' => 'jpg',
        ], Config::get('uploads.allowed_mimes'));
    }


}