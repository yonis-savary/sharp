mkdir tests/Manual-test-app
echo "{\"require\": {\"yonis-savary/sharp\": \"dev-main\"}, \"repositories\": [{\"type\": \"path\", \"url\": \"$(pwd)\"}]}" > ./tests/Manual-test-app/composer.json
cd tests/Manual-test-app
composer install
composer exec sharp-install
php do initialize
cd ..