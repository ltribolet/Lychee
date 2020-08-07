<p align="center"><img src="https://raw.githubusercontent.com/LycheeOrg/Lychee/master/Banner.png" width="400px" alt="@LycheeOrg"></p>

#### A great looking and easy-to-use photo-management-system.

![Lychee](https://camo.githubusercontent.com/b9010f02c634219795950e034f511f4cf4af5c60/68747470733a2f2f732e656c6563746572696f75732e636f6d2f696d616765732f6c79636865652f312e6a706567)
![Lychee](https://camo.githubusercontent.com/5484591f0b15b6ba27d4845b292cc5d3a988b3b9/68747470733a2f2f732e656c6563746572696f75732e636f6d2f696d616765732f6c79636865652f322e6a706567)

Lychee is a free photo-management tool, which runs on your server or web-space. Installing is a matter of seconds. Upload, manage and share photos like from a native application. Lychee comes with everything you need and all your photos are stored securely. Read more on our [website](https://LycheeOrg.github.io).

## Installation

To run Lychee, everything you need is a web-server with PHP 7.3 or later and a MySQL-Database. Follow the instructions to install Lychee on your server. This version of Lychee is built on the Laravel framework. To install:

1. Clone this repo to your server and set the web root to `lychee/public`
2. Run `composer install --no-dev` to install dependencies
3. Copy `.env.example` as `.env` and edit it to match your parameters
4. Generate your secret key with `php artisan key:generate`
5. Migrate your database with `php artisan migrate` to create a new database or migrate an existing Lychee installation to the latest framework.

See detailed instructions on the [Installation](https://lycheeorg.github.io/docs/installation.html) page of our documentation.

### Docker Dev Environment

1. Clone this repo
2. Run `make local-dev-install`
3. Add `lychee.test` to your `/etc/hosts` to point to `0.0.0.0`
4. Go to [http://lychee.test](http://lychee.test)
5. Log in with lychee/lychee

### Settings

Sign in and click the gear in the top left corner to change your settings. [Settings &#187;][1]

### Local Sample Gallery

* Be sure to have the `APP_ENV` set to local.
* You'll need an [https://unsplash.com/developers](Unsplash Developer account) in order to use the seed.
* Create an application with your new account.
* It can remain a non production application, the 50 reqs/hour rate should be enough.
* Once created, fill in `UNSPLASH_SECRET` and `UNSPLASH_APP_ID` in your `.env` file.

Finally execute:
```
make sample-gallery
```

## Advanced Features

Lychee is ready to use straight after installation, but some features require a little more configuration.

### Keyboard Shortcuts

These shortcuts will help you to use Lychee even faster. [Keyboard Shortcuts &#187;](https://lycheeorg.github.io/docs/keyboard.html)

### Dropbox import

In order to use the Dropbox import from your server, you need a valid drop-ins app key from [their website](https://www.dropbox.com/developers/apps/create). Lychee will ask you for this key, the first time you try to use the import. Want to change your code? Take a look at [the settings][1] of Lychee.

### Twitter Cards

Lychee supports [Twitter Cards](https://dev.twitter.com/docs/cards) and [Open Graph](http://opengraphprotocol.org) for shared images (not albums). In order to use Twitter Cards you need to request an approval for your domain. Simply share an image with Lychee, copy its link and paste it in [Twitters Card Validator](https://dev.twitter.com/docs/cards/validation/validator).

### Imagick

Lychee uses [Imagick](https://www.imagemagick.org) when installed on your server. In this case you will benefit from a faster processing of your uploads, better looking thumbnails and intermediate sized images for small screen devices. You can disable the usage of [Imagick](https://www.imagemagick.org) in the [settings][1].

## Troubleshooting

Take a look at the [Documentation](https://lycheeorg.github.io/docs/), particularly the [FAQ](https://lycheeorg.github.io/docs/faq.html) if you have problems. Discovered a bug? Please create an issue [here](https://github.com/LycheeOrg/Lychee/issues) on GitHub!

[1]: https://lycheeorg.github.io/docs/settings.html
