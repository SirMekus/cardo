# Installation

On your local machine:

1. Unzip the file
1. Run composer install
1. Create and fill the .env file (example included in .env-example)
1. Run `php artisan migrate` to create database tables
1. Seed the database by running `php artisan db:seed --class=MerchantSeeder`.
1. Seed the database by running `php artisan db:seed --class=UserSeeder` (optional).
1. Seed the database by running `php artisan db:seed --class=CardSeeder` (optional).

> NB: If you wish to set up your own API client side, the base url should be http://localhost or whatever it is that is your local server's address. All requests must then be passed via the `"api/"` path. This has already been taken care of if you use the Postman Collection included in the project. Simply import the Postman Collection into your own Postman software and explore the routes present there.
> Please note that you can actually edit the `'app_url'` variable in the Postman Environment (present in this project) to reflect your local environment's server/host or our live server which can be found in the `".env"` file.

## Authentication

You must first register and/or login before you can explore the API using the endpoints presented in the project. After a successful registration or login, a token is sent as part of the response. This token should be passed as a Bearer token in the header of subsequent calls to the endpoints.

This token expires on logout and a new one will be created when you login again. The 'updated' token should then be used for subequent requests. If you use the Postman Collection included in this project, in the Environment, set the `token` variable to the value of the token sent from the server and that is all. The endpoints are present in the Postman collection and can be found in the `"api.php"` file in the `"routes"` folder.

> Note that you should log in with the credentials you used in registeration.