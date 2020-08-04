<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\ModelFunctions\ConfigFunctions;
use App\Models\Configs;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * @var ConfigFunctions
     */
    private $configFunctions;

    public function __construct(ConfigFunctions $configFunctions)
    {
        $this->configFunctions = $configFunctions;
    }

    /**
     * given a URL: http://example.com/<something>
     * fetches in the tables if the page <something> exists and returns it
     * return 404 otherwise.
     */
    public function page(Request $request, string $page): View
    {
        $pageModel = Page::enabled()->where('link', '/' . $page)->first();

        if ($pageModel === null) {
            \abort(404);
        }

        $lang = \trans('messages');
        $lang['language'] = App::getLocale();

        $infos = $this->configFunctions->get_pages_infos();
        $title = Configs::get_value('site_title', Config::get('defines.defaults.SITE_TITLE'));
        $menus = Page::menu()->get();

        $contents = $pageModel->content;
        $page_config = [];
        $page_config['show_hosted_by'] = false;
        $page_config['display_socials'] = false;

        return \view(
            'page',
            [
                'locale' => $lang,
                'title' => $title,
                'infos' => $infos,
                'menus' => $menus,
                'contents' => $contents,
                'page_config' => $page_config,
            ]
        );
    }

    /**
     * TODO: add function to allow the edition of pages.
     */
    public function edit(Request $request, string $page): void
    {
    }

    /**
     * TODO: add function to save the edition of pages.
     */
    public function save(Request $request, string $page): void
    {
    }
}
