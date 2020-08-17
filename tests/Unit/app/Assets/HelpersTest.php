<?php

namespace Tests\Unit\app\Assets;

use App\Assets\Helpers;
use App\Exceptions\DivideByZeroException;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Tests\Unit\UnitTestCase;

class HelpersTest extends UnitTestCase
{
    public function testCacheBustingFileExists()
    {
        $filePath = '/this/is/a/filepath/file';
        File::shouldReceive('exists')->andReturn(true);
        $now = \time();
        File::shouldReceive('lastModified')->andReturn($now);

        $fileCache = Helpers::cacheBusting($filePath);

        static::assertSame(\sprintf('%s?%s', $filePath, $now), $fileCache);

    }

    public function testCacheBustingFileDoNotExists()
    {
        $filePath = '/this/is/a/filepath/file';
        File::shouldReceive('exists')->andReturn(false);

        $fileCache = Helpers::cacheBusting($filePath);

        static::assertSame($filePath, $fileCache);

    }

    public function testGetDeviceType()
    {
        App::shouldReceive('make')->andReturn($this->createMock(Repository::class));
        $type = Helpers::getDeviceType();

        // There's really not point testing that.
        static::assertEmpty($type);
    }

    public function testTrancateIf32WithoutPreviousShortId()
    {
        $id = (string) \random_int(100000, 1000000);

        $shortId = Helpers::trancateIf32($id);

        static::assertSame($id, $shortId);
    }

    public function testTrancateIf32WithPreviousShortId()
    {
        $id = (string) \random_int(100000, 1000000);
        $previousId = (string) \random_int(100000, 1000000);

        $shortId = Helpers::trancateIf32($id, $previousId);

        static::assertSame($id, $shortId);
    }

    public function testGetExtension()
    {
        $filename = 'tests/Feature/night.jpg';
        $expectedExtension = '.jpg';

        $extension = Helpers::getExtension($filename, false);

        static::assertSame($expectedExtension, $extension);
    }

    public function testGetExtensionUrl()
    {
        $filename = 'http://username:password@hostname:9090/path?arg=value#anchor';
        $expectedExtension = '';

        $extension = Helpers::getExtension($filename, true);

        static::assertSame($expectedExtension, $extension);
    }

    public function testGetExtensionSpecialCase()
    {
        $filename = 'tests/Feature/night.jpg:gif';
        $expectedExtension = '.jpg';

        $extension = Helpers::getExtension($filename, true);

        static::assertSame($expectedExtension, $extension);
    }

    /**
     * @dataProvider permissionsProvider
     */
    public function testHasPermissions(bool $isExists, bool $isReadable, bool $isWritable, bool $expected): void
    {
        File::shouldReceive('exists')->times(1)->andReturn($isExists);
        File::shouldReceive('isReadable')->times((int) $isExists)->andReturn($isReadable);
        File::shouldReceive('isWritable')->times((int) ($isExists && $isReadable))->andReturn($isWritable);

        $return = Helpers::hasPermissions('tests/Feature/night.jpg');

        static::assertSame($expected, $return);
    }

    /**
     * @return array<string, array<bool>>
     */
    public function permissionsProvider(): array
    {
        return [
            'all ok '  => [true, true, true, true],
            'not exists' => [false, true, true, false],
            'not readable ' => [true, false, true, false],
            'not writable'  => [true, true, false, false],
            'not exists & not readable'  => [false, false, true, false],
            'not exists & not writable'  => [false, true, false, false],
            'not readable & not writable'  => [true, false, false, false],
            'not exists & not readable & not writable'  => [false, false, false, false],
        ];
    }

    public function testGcd(): void
    {
        $number = 50;
        $divider = 100;
        $expected = 50;
        $output = Helpers::gcd($number, $divider);
        static::assertSame($expected, $output);

        $number = 500;
        $divider = 100;
        $expected = 100;
        $output = Helpers::gcd($number, $divider);
        static::assertSame($expected, $output);

        $number = 500;
        $divider = 0;
        $this->expectException(DivideByZeroException::class);
        Helpers::gcd($number, $divider);
    }

    public function testStrOfBool()
    {
        $expectedTrue = '1';
        $expectedFalse = '0';
        $true = Helpers::str_of_bool(true);
        $false = Helpers::str_of_bool(false);

        static::assertSame($expectedTrue, $true);
        static::assertSame($expectedFalse, $false);
    }

    public function testEx2x()
    {
        $expected = 'tests/Feature/night@2x.jpg';
        $ex2x = Helpers::ex2x('tests/Feature/night.jpg');
        static::assertSame($expected, $ex2x);

        $this->expectException(\ErrorException::class);
        Helpers::ex2x('tests/Feature/night');
    }
}
