<?php

namespace Tests\Feature\API;

use App\Models\Album;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\URL;
use Tests\Feature\FeatureTestCase;

class AlbumsAPITest extends FeatureTestCase
{
    use OpenApiSchemaValidator;

    protected Collection $albums;
    protected Album $soloPublicAlbum;

    public function setUp(): void
    {
        parent::setUp();
        $this->schemaFile = \base_path('.openapi/openapi.yml');
        $this->soloPublicAlbum = \factory(Album::class)->create([
            'public' => true,
            'owner_id' => 99,
        ]);

        $this->albums = \factory(Album::class, 4)->create(['owner_id' => $this->user->id]);

        // New unsorted photo
        \factory(Photo::class)->create();
    }

    public function testAlbumsListAsAdmin(): void
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

        $response = $this->get(URL::route('albums.index'));

        static::assertTrue($this->schemaValidate('/albums', 'get', $response));
        $response->assertJsonFragment($expectedData);
        $response->assertJsonPath('smart_albums.unsorted.num', 1);
    }

    public function testAlbumsListAsGuest(): void
    {
        $response = $this->get(URL::route('albums.index'));

        static::assertTrue($this->schemaValidate('/albums', 'get', $response));
        $actual = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        static::assertCount(0, $actual['shared_albums']);
        static::assertCount(0, $actual['smart_albums']);
        static::assertCount(1, $actual['albums']);
        static::assertSame($this->soloPublicAlbum->id, $actual['albums'][0]['id']);
    }
}
