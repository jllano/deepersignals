#  DEEPERSIGNALS API
***
A simple API that allows to accept CSV file which represents a company team hierarchy and returns a nested json structure that represents the hierarchy of the company.


### Requirements:
```
PHP 8.3 +
composer
```
### Installation:
```
composer install
```

### Running the Application:
```
symfony server:start
```

### Sample Usage:
```
 curl -X POST "http://127.0.0.1:8000/api/import-team" \        
  -F "file=@/path_to_your_csv_file.csv" \
  -H "auth-token:your_secure_token_here"
```
> If you want to filter the response by a specific team name, you can add the query parameter `?q=Sales` to the request.

### NOTE:
```
make sure to replace the auth-token with the one setup in your env config.
```

### Unit Tests:
```
php bin/phpunit
```