<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use App\Models\Album;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\URL;
use Tests\Feature\FeatureTestCase;

class AlbumAPITest extends FeatureTestCase
{
    use OpenApiSchemaValidator;

    protected Collection $albums;

    protected Album $soloPublicAlbum;

    protected function setUp(): void
    {
        parent::setUp();
        $this->schemaFile = \base_path('.openapi/openapi.yml');
        $this->soloPublicAlbum = \factory(Album::class)->create([
            'public' => true,
            'owner_id' => 99,
        ]);

        $this->albums = \factory(Album::class, 4)->create(['owner_id' => $this->user->id]);

        \factory(Photo::class, 10)->create([
            'album_id' => $this->albums->first()->id,
        ]);
    }

    public function testAlbumsGetAsAdmin(): void
    {
        $this->actingAs($this->user);
        $data = $this->albums->first();
        $expectedData = [
            'id' => $data->id,
            'title' => $data->title,
            'public' => (bool) $data->public,
            'visible' => (bool) $data->visible_hidden,
            'parent_id' => $data->parent_id,
            'description' => $data->description,
        ];

        $response = $this->get(URL::route('albums.index', ['album' => $data->id]));

        static::assertTrue($this->schemaValidate('/albums/{albumId}', 'get', $response));
        $response->assertJsonFragment($expectedData);
    }

    public function testAlbumsListAsGuest(): void
    {
        $data = $this->soloPublicAlbum;
        $expectedData = [
            'id' => $data->id,
            'title' => $data->title,
            'public' => (bool) $data->public,
            'visible' => (bool) $data->visible_hidden,
            'parent_id' => $data->parent_id,
            'description' => $data->description,
        ];

        $response = $this->get(URL::route('albums.index', ['album' => $data->id]));

        static::assertTrue($this->schemaValidate('/albums/{albumId}', 'get', $response));
        $response->assertJsonFragment($expectedData);
    }
}
