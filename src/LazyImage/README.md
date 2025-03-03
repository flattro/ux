# Symfony UX LazyImage

Symfony UX LazyImage is a Symfony bundle providing utilities to improve
image loading performance. It is part of [the Symfony UX initiative](https://symfony.com/ux).

It provides two key features:

-   a Stimulus controller to load lazily heavy images, with a placeholder
-   a [BlurHash implementation](https://blurha.sh/) to create data-uri thumbnails for images

## Installation

Symfony UX LazyImage requires PHP 7.2+ and Symfony 4.4+.

You can install this bundle using Composer and Symfony Flex:

```sh
composer require symfony/ux-lazy-image

# Don't forget to install the JavaScript dependencies as well and compile
yarn install --force
yarn encore dev
```

Also make sure you have at least version 2.0 of [@symfony/stimulus-bridge](https://github.com/symfony/stimulus-bridge)
in your `package.json` file.

## Usage

The default usage of Symfony UX LazyImage is to use its Stimulus controller to first load
a small placeholder image that will then be replaced by the high-definition version once the
page has been rendered:

```twig
<img
    src="{{ asset('image/small.png') }}"
    {{ stimulus_controller('symfony/ux-lazy-image/lazy-image') }}
    data-hd-src="{{ asset('image/large.png') }}"

    srcset="{{ asset('image/small.png') }} 1x, {{ asset('image/small2x.png') }} 2x"
    data-hd-srcset="{{ asset('image/large.png') }} 1x, {{ asset('image/large2x.png') }} 2x"

    {# Optional but avoids having a page jump when the image is loaded #}
    width="200"
    height="150"
/>
```

**Note** The `stimulus_controller()` function comes from
[WebpackEncoreBundle v1.10](https://github.com/symfony/webpack-encore-bundle).

Instead of using a generated thumbnail that would exist on your filesystem, you can use
the BlurHash algorithm to create a light, blurred, data-uri thumbnail of the image:

```twig
<img
    src="{{ data_uri_thumbnail('public/image/large.png', 100, 75) }}"
    {{ stimulus_controller('symfony/ux-lazy-image/lazy-image') }}
    data-hd-src="{{ asset('image/large.png') }}"

    {# Using BlurHash, the size is required #}
    width="200"
    height="150"
/>
```

The `data_uri_thumbnail` function receives 3 arguments:

-   the server path to the image to generate the data-uri thumbnail for ;
-   the width of the BlurHash to generate
-   the height of the BlurHash to generate

You should try to generate small BlurHash images as generating the image can be CPU-intensive.
Instead, you can rely on the browser scaling abilities by generating a small image and using the
`width` and `height` HTML attributes to scale up the image.

### Extend the default behavior

Symfony UX LazyImage allows you to extend its default behavior using a custom Stimulus controller:

```js
// mylazyimage_controller.js

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.element.addEventListener('lazy-image:connect', this._onConnect);
        this.element.addEventListener('lazy-image:ready', this._onReady);
    }

    disconnect() {
        // You should always remove listeners when the controller is disconnected to avoid side-effects
        this.element.removeEventListener('lazy-image:connect', this._onConnect);
        this.element.removeEventListener('lazy-image:ready', this._onReady);
    }

    _onConnect(event) {
        // The lazy-image behavior just started
    }

    _onReady(event) {
        // The HD version has just been loaded
    }
}
```

Then in your template, add your controller to the HTML attribute:

```twig
<img
    src="{{ data_uri_thumbnail('public/image/large.png', 100, 75) }}"
    {{ stimulus_controller({
        mylazyimage: {},
        'symfony/ux-lazy-image/lazy-image': {}
    }) }}
    data-hd-src="{{ asset('image/large.png') }}"

    {# Using BlurHash, the size is required #}
    width="200"
    height="150"
/>
```

> **Note**: be careful to add your controller **before** the LazyImage controller so that
> it is executed before and can listen on the `lazy-image:connect` event properly.

## Backward Compatibility promise

This bundle aims at following the same Backward Compatibility promise as the Symfony framework:
[https://symfony.com/doc/current/contributing/code/bc.html](https://symfony.com/doc/current/contributing/code/bc.html)

However it is currently considered
[**experimental**](https://symfony.com/doc/current/contributing/code/experimental.html),
meaning it is not bound to Symfony's BC policy for the moment.

## Run tests

### PHP tests

```sh
php vendor/bin/phpunit
```

### JavaScript tests

```sh
cd Resources/assets
yarn test
```
