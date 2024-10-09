mkdir test-app
echo "{\"require\": {\"yonis-savary/sharp\": \"dev-main\"}, \"repositories\": [{\"type\": \"path\", \"url\": \"$(pwd)\"}]}" > ./test-app/composer.json
cd test-app
composer install
cp -r vendor/yonis-savary/sharp/src/Core/Server/* .
cd ..