<?php

namespace Tests\Feature\API;

use App\Models\Album;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\URL;
use Tests\Feature\FeatureTestCase;

class AlbumsAPITest extends FeatureTestCase
{
    use OpenApiSchemaValidator;

    protected Collection $albums;
    protected Album $soloSharedAlbum;

    public function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->user);
        $this->schemaFile = \base_path('.openapi/openapi.yml');
        $this->soloSharedAlbum = \factory(Album::class)->create([
            'public' => true,
            'owner_id' => 99,
        ]);

        $this->albums = \factory(Album::class, 4)->create();
    }

    public function testUsersList(): void
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
    }
}
