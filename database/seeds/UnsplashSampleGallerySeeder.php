<?php

declare(strict_types=1);

use App\ModelFunctions\PhotoFunctions;
use App\Models\Album;
use App\Models\User;
use Crew\Unsplash\ArrayObject;
use Crew\Unsplash\Exception as UnsplashException;
use Crew\Unsplash\HttpClient as UnsplashClient;
use Crew\Unsplash\Photo as UnsplashPhoto;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UnsplashSampleGallerySeeder extends Seeder
{
    private Client $client;

    private PhotoFunctions $photoFunctions;

    public function __construct(PhotoFunctions $photoFunctions, Client $client)
    {
        $this->photoFunctions = $photoFunctions;
        $this->client = $client;
    }

    public function run(): void
    {
        if (! App::environment('local')) {
            return;
        }

        $user = User::getAdmin();

        if (! $user) {
            Log::error('You need at least the Admin user to be in');

            return;
        }

        $unsplashConfig = Config::get('services.unsplash');

        if (! $unsplashConfig['applicationId']) {
            Log::error('You need an Unsplash Developer account in order to use this seeder.');

            return;
        }

        UnsplashClient::init($unsplashConfig);

        $albums = \factory(Album::class, 5)->create([
            'owner_id' => $user->id,
        ]);

        $albums->each(function (Album $album, int $key): void {
            try {
                $photos = UnsplashPhoto::all($key + 1, 10);
                $this->savePhotos($photos, $album);
            } catch (UnsplashException $e) {
                Log::error($e->getMessage());

                return;
            }
        });
    }

    private function savePhotos(ArrayObject $photos, Album $album): void
    {
        foreach ($photos as $remotePhoto) {
            $link = $remotePhoto->download();

            $name = (Str::limit(Str::slug($remotePhoto->description), 20) ?: \uniqid()) . '.jpg';
            $file = \sys_get_temp_dir() . '/' . $name;
            $this->downloadFile($file, $link);

            $output = $this->photoFunctions->add(
                [
                    'type' => 'photo',
                    'tmp_name' => $file,
                    'name' => $name,
                ],
                $album->id
            );

            if ($output !== true && ! is_int($output)) {
                Log::warning(\sprintf('Impossible to add the photo, reason: %s', $output));
            }
        }
    }

    private function downloadFile(string $file, string $link): void
    {
        $fileHandle = \fopen($file, 'wb+');

        try {
            $this->client->get($link, [RequestOptions::SINK => $fileHandle]);
        } catch (RequestException $e) {
            throw $e;
        } finally {
            @fclose($fileHandle);
        }
    }
}
