<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\ModelFunctions\ConfigFunctions;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Configs;
use App\Models\Page;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

class IndexController extends Controller
{
    private ConfigFunctions $configFunctions;

    private SymLinkFunctions $symLinkFunctions;

    public function __construct(ConfigFunctions $configFunctions, SymLinkFunctions $symLinkFunctions)
    {
        $this->configFunctions = $configFunctions;
        $this->symLinkFunctions = $symLinkFunctions;
    }

    /**
     * Display the landing page if enabled
     * otherwise display the gallery.
     */
    public function show(): View
    {
        if (Configs::get_value('landing_page_enable', '0') === '1') {
            $lang = \trans('messages');
            $lang['language'] = App::getLocale();

            $infos = $this->configFunctions->get_pages_infos();

            $menus = Page::menu()->get();

            $title = Configs::get_value('site_title', Config::get('defines.defaults.SITE_TITLE'));

            $page_config = [];
            $page_config['show_hosted_by'] = false;
            $page_config['display_socials'] = false;

            return \view(
                'landing',
                [
                    'locale' => $lang,
                    'title' => $title,
                    'infos' => $infos,
                    'menus' => $menus,
                    'page_config' => $page_config,
                ]
            );
        }

        return $this->gallery();
    }

    /**
     * Just call the phpinfo function.
     * Cannot be tested.
     */
    // @codeCoverageIgnoreStart
    public function phpinfo(): string
    {
        return (string) \phpinfo();
    }

    // @codeCoverageIgnoreEnd

    /**
     * Display the gallery.
     */
    public function gallery(): View
    {
        $this->symLinkFunctions->remove_outdated();
        $infos = $this->configFunctions->get_pages_infos();

        $lang = \trans('messages');
        $lang['language'] = App::getLocale();

        $title = Configs::get_value('site_title', Config::get('defines.defaults.SITE_TITLE'));
        $page_config = [];
        $page_config['show_hosted_by'] = true;
        $page_config['display_socials'] = Configs::get_value('display_social_in_gallery', '0') === '1';

        return \view(
            'gallery',
            [
                'locale' => $lang,
                'title' => $title,
                'infos' => $infos,
                'page_config' => $page_config,
            ]
        );
    }
}
