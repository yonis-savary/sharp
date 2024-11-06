mkdir test-app
echo "{\"require\": {\"yonis-savary/sharp\": \"dev-main\"}, \"repositories\": [{\"type\": \"path\", \"url\": \"$(pwd)\"}]}" > ./test-app/composer.json
cd test-app
composer install
composer exec sharp-install
php do initialize
cd ..