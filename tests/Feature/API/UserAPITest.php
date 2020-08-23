<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\URL;
use Tests\Feature\FeatureTestCase;

class UserAPITest extends FeatureTestCase
{
    use OpenApiSchemaValidator;

    /**
     * @var Collection
     */
    protected $users;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->user);
        $this->schemaFile = \base_path('.openapi/openapi.yml');
        $this->users = \factory(User::class, 5)->create();
    }

    public function testUsersList(): void
    {
        $response = $this->get(URL::route('users.index'));

        static::assertTrue($this->schemaValidate('/users', 'get', $response));
        $response->assertJsonFragment($this->users->first()->toArray());
    }

    public function testUsersStore(): void
    {
        $parameters = [
            'username' => 'username',
            'password' => 'password',
            'lock' => '1',
            'upload' => '1',
        ];

        $response = $this->post(URL::route('users.store'), $parameters);

        static::assertTrue($this->schemaValidate('/users', 'post', $response));
        $response->assertSee('true');
    }

    public function testUsersUpdate(): void
    {
        $userData = $this->users->first()->toArray();

        $userData['username'] = 'AnotherUsername';
        $userData['lock'] = '1';

        $response = $this->put(URL::route('users.update', ['user' => $userData['id']]), $userData);

        static::assertTrue($this->schemaValidate('/users/{user}', 'put', $response));
        $response->assertSee('true');
    }

    public function testUsersDelete(): void
    {
        $userData = $this->users->last()->toArray();

        $response = $this->delete(URL::route('users.destroy', ['user' => $userData['id']]));

        static::assertTrue($this->schemaValidate('/users/{user}', 'delete', $response));
        $this->assertDatabaseMissing('users', ['id' => $userData['id']]);
    }
}
