After creating the containers, run:
```bash
docker exec laranab-app composer install
docker exec php artisan migrate --force
```

Also, if you wish to bind mount the 'db' directory for the database, create it first.