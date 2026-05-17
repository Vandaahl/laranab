When you have cloned the repository, copy the .env.example file to .env and fill in the values.

After creating the containers, run:
```bash
docker exec laranab-app composer install
docker exec laranab-app php artisan migrate --force
docker exec laranab-app php artisan storage:link
```

If you wish to bind mount the 'db' directory for the database, create it first.

When the containers are up and running, run the following command to generate the application key:

```bash
docker exec laranab php artisan key:generate --show
```

Copy the key and paste it in the .env file after the APP_KEY item, so it looks like this:

```bash
APP_KEY=PUT_YOUR_KEY_HERE
```

Then restart the containers.