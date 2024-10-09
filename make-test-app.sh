mkdir test-app
cd test-app
echo '{"require": {"yonis-savary/sharp": "dev-main"}, "repositories": [{"type": "path", "url": "/home/yonis/.config/utilux/git-utils/repositories/yonis-savary/sharp/"}]}' > ./composer.json
composer install
cp -r vendor/yonis-savary/sharp/src/Core/Server/* .
cd ..