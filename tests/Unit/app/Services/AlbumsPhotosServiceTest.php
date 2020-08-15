<?php

namespace Tests\Unit\app\Services;

use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use App\Services\AlbumFactory;
use App\Services\AlbumsPhotosService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AlbumsPhotosServiceTest extends TestCase
{
    public function testGetAlbumsPhotosThatCanBeSeen(): void
    {
        Auth::shouldReceive('user')->andReturn($this->createMock(User::class));
        $builderMock = $this->createMock(Builder::class);
        $builderMock->method('get')->willReturn([$this->createMock(Photo::class)]);
        $albumMock = $this->createMock(Album::class);
        $albumMock->method('canBeSeenBy')->willReturn(true);
        $albumMock->method('getAvailablePhotos')->willReturn($builderMock);
        $hasManyMock = $this->createMock(HasMany::class);
        $hasManyMock->method('get')->willReturn([]);
        $albumMock->method('children')->willReturn($hasManyMock);
        $albumFactoryMock = $this->createMock(AlbumFactory::class);
        $albumFactoryMock->method('getAlbum')->willReturn($albumMock);

        $albumsPhotosService = new AlbumsPhotosService($albumFactoryMock);
        $albums = $albumsPhotosService->getAlbumsPhotos([1]);

        static::assertCount(1, $albums);
        static::assertInstanceOf(Album::class, $albums[0]['album']);
        static::assertArrayHasKey('photos', $albums[0]['content']);
        static::assertArrayNotHasKey('children', $albums[0]['content']);
        static::assertCount(1, $albums[0]['content']['photos']);
        static::assertInstanceOf(Photo::class, $albums[0]['content']['photos'][0]);
    }

    public function testGetAlbumsPhotosThatCannotBeSeen(): void
    {
        Auth::shouldReceive('user')->andReturn($this->createMock(User::class));

        $albumMock = $this->createMock(Album::class);
        $albumMock->method('canBeSeenBy')->willReturn(false);

        $albumFactoryMock = $this->createMock(AlbumFactory::class);
        $albumFactoryMock->method('getAlbum')->willReturn($albumMock);

        $albumsPhotosService = new AlbumsPhotosService($albumFactoryMock);
        $albums = $albumsPhotosService->getAlbumsPhotos([1]);

        static::assertCount(0, $albums);
    }

    public function testGetAlbumPhotosWithoutChildren()
    {
        $albumFactoryMock = $this->createMock(AlbumFactory::class);
        $builderMock = $this->createMock(Builder::class);
        $builderMock->method('get')->willReturn([$this->createMock(Photo::class)]);
        $albumMock = $this->createMock(Album::class);
        $albumMock->method('getAvailablePhotos')->willReturn($builderMock);
        $hasManyMock = $this->createMock(HasMany::class);
        $hasManyMock->method('get')->willReturn([]);
        $albumMock->method('children')->willReturn($hasManyMock);

        $albumsPhotosService = new AlbumsPhotosService($albumFactoryMock);
        $files = $albumsPhotosService->getAlbumPhotos($albumMock);

        static::assertArrayHasKey('photos', $files);
        static::assertArrayNotHasKey('children', $files);
        static::assertCount(1, $files['photos']);
        static::assertInstanceOf(Photo::class, $files['photos'][0]);
    }

    public function testGetAlbumPhotosWithChildren()
    {
        $albumFactoryMock = $this->createMock(AlbumFactory::class);
        $builderMock = $this->createMock(Builder::class);
        $builderMock->method('get')->willReturn([$this->createMock(Photo::class)]);

        $albumMock = $this->createMock(Album::class);
        $albumMock->method('getAvailablePhotos')->willReturn($builderMock);

        $childrenAlbumMock = $this->createMock(Album::class);
        $childrenAlbumMock->method('getAvailablePhotos')->willReturn($builderMock);
        $childrenHasManyMock = $this->createMock(HasMany::class);
        $childrenHasManyMock->method('get')->willReturn([]);
        $childrenAlbumMock->method('children')->willReturn($childrenHasManyMock);

        $hasManyMock = $this->createMock(HasMany::class);
        $hasManyMock->method('get')->willReturn([$childrenAlbumMock]);
        $albumMock->method('children')->willReturn($hasManyMock);

        $albumsPhotosService = new AlbumsPhotosService($albumFactoryMock);
        $files = $albumsPhotosService->getAlbumPhotos($albumMock);

        static::assertArrayHasKey('photos', $files);
        static::assertArrayHasKey('children', $files);
        static::assertCount(1, $files['photos']);
        static::assertInstanceOf(Photo::class, $files['photos'][0]);
        static::assertInstanceOf(Album::class, $files['children'][0]['album']);
        static::assertArrayHasKey('photos', $files['children'][0]['content']);
        static::assertArrayNotHasKey('children', $files['children'][0]['content']);
    }

    public function testGetAlbumPhotosWithoutAnyPhotos()
    {
        $albumFactoryMock = $this->createMock(AlbumFactory::class);
        $builderMock = $this->createMock(Builder::class);
        $builderMock->method('get')->willReturn([]);
        $albumMock = $this->createMock(Album::class);
        $albumMock->method('getAvailablePhotos')->willReturn($builderMock);
        $hasManyMock = $this->createMock(HasMany::class);
        $hasManyMock->method('get')->willReturn([]);
        $albumMock->method('children')->willReturn($hasManyMock);

        $albumsPhotosService = new AlbumsPhotosService($albumFactoryMock);
        $files = $albumsPhotosService->getAlbumPhotos($albumMock);

        static::assertArrayNotHasKey('photos', $files);
        static::assertArrayNotHasKey('children', $files);
        static::assertEmpty($files);
    }
}
