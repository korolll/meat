lock "~> 3.10.2"

set :format_options, log_file: "storage/logs/capistrano.log"

set :application, "api"
set :keep_releases, 1
set :repo_url, "git@bitbucket.org:tealsy/api.git"
set :branch, ENV.fetch('REVISION', 'dev')

append :linked_files,
  ".env"

append :linked_dirs,
  "node_modules",
  "storage",
  "vendor"

after 'deploy:updated', :post_updated do
  on roles :www do
    within release_path do
      # Установка PHP-зависимостей
      execute :composer, "install --no-dev --quiet --prefer-dist --optimize-autoloader"

      # Кэшируем конфиги
      execute :php, "artisan config:cache", raise_on_non_zero_exit: false

      # Кэшируем роуты
      execute :php, "artisan route:cache", raise_on_non_zero_exit: false

      # Линкуем публичный диск
      execute :php, "artisan storage:link"

      # Установка прав на запись
      execute :chmod, "-R ug+rwx #{release_path}/bootstrap/cache #{shared_path}/storage"

      # Установка группы для директорий под запись
      execute :chgrp, "-R www-data #{release_path}/bootstrap/cache #{shared_path}/storage"
    end
  end

  on roles :db do
    within release_path do
      # Применение миграций
      execute :php, "artisan migrate --force"
    end
  end
end

after 'deploy:symlink:release', :post_release do
  on roles :fpm do
    within release_path do
      # Чтобы сбросить OPCache перезагружаем php-fpm
      execute :sudo, "service php7.2-fpm restart"
    end
  end

  on roles :npm do
    within release_path do
      # Установка NPM-зависимостей
      execute :npm, "ci --silent"
    end
  end

  on roles :doc do
    within release_path do
      # Сборка документации по методам API
      execute :npm, "run doc-build-all &> #{release_path}/storage/logs/spectacle.log", raise_on_non_zero_exit: false

      # Сборка документации по базе данных
      execute :schemaspy, "#{release_path}/public/documentation/database/schema &> #{release_path}/storage/logs/schemaspy.log", raise_on_non_zero_exit: false
    end
  end
end
