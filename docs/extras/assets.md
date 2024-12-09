[< Back to summary](../README.md)

# 🎨 Asset Serving

The `AssetServer` component is here to help you serve your assets (CSS/JS/IMG...etc.)

This component was made to make the application directory cleaner by putting assets in a `Assets` directory and not `Public`

## Organize your assets

Your applications assets should be stored in `<YourApp>/Assets` (They can be stored in subdirectories)

## Configuration

Here is the default configuration for `AssetServer`

```json
"asset-server": {
    "enabled": true,
    "url": "/assets",
    "middlewares": [],
    "max-age": false
}
```

- setting `enabled` to `true` make sure the component analyze any incoming request, you can set it to `false` to disable the component
- `url` define with URL is used to serve assets
- `middlewares` is used to add security layers to your assets (example: you can make them only accessible to authenticated user)
- setting `max-age` to an integer allows your assets to be cached by the browser (example: 3600 = 1 hour of cache time to live)

## Serving

Let's imagine your `assets` directory got those files

- `assets/js/shipping.js`
- `assets/css/shipping.css`
- `assets/js/contact/creation.js`
- `assets/js/product/creation.js`

Getting resources url is pretty straightforward, you can use

```AssetsServer::getInstance()->getURL($x)```

To get your assets, put this in your view
```php
<link
    rel="stylesheet"
    href="<?= AssetsServer::getInstance()->getURL($x) ?>"
>
<script
    src="<?= AssetsServer::getInstance()->getURL('shipping.js') ?>"
></script>
```
But writing this is quite long and not very readable.

To address this, the [`helper-web.php`](../../src/Helpers/helpers-web.php) got three useful functions

```php
<!-- Put the script and stylesheet with it URL -->
<?= script('shipping.js') ?>
<?= style('shipping.css') ?>

<!-- Inject it directly by reading the file ! -->
<?= script('shipping.js', true) ?>
<?= style('shipping.css', true) ?>

<!-- The asset() function can be used to get an URL -->
<link rel='stylesheet' href="<?= asset('myStyle.css') ?>">

```

### Be precise with your paths

In our previous example, two of our assets got the same basename:
- `assets/js/contact/creation.js`
- `assets/js/product/creation.js`

If you try to fetch `creation.js`, it will work, but it may not work in the
way you intended it.

When listing files, `AssetServer` will take the first matching file and assume it
is the one we are looking for

To address this, your can be more precise when giving an asset name

```php
script('contact/creation.js')
```


## Node packages serving

The `AssetServer` can also serve files from your `node_module` directory.

With this configuration
```json
"asset-server": {
    "node-packages": [
        "bootstrap-icons"
    ]
}
```

`AssetServer` will serve every files that are indexed in `bootstrap-icons/package.json` (`files` key)

You can also add packages to serve with the `publish-node-package`

```bash
php do publish-node-package bootstrap-icons
```

[< Back to summary](../README.md)