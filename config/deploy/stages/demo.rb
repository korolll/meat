server '185.43.6.87', user: 'root', roles: %w{www fpm db npm doc}

set :deploy_to, "/var/www/tealsy.demoapi.ru"
