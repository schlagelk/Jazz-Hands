# Jazz-Hands
Uses Slim 3, Laravel's Eloquent ORM, Bootstrap to provide a starting application which supports user authentication and email confirmation.

Make a copy of app/bootstrap/.env.sample into a file called .env and set your database credentials in the first section, your SMTP mail settings in the second (this uses the PHPMailer package) and your base url in the third.

These values are loaded into bootstrap/app.php where you see getenv('xxx')

Run `composer install` to load dependencies

Run `vendor/bin/phinx init` to create a phinx.yml file and enter your db credentials in there

Run `vendor/bin/phinx migrate` to create the default users table

This will now support standard user authentication with required email confirmation.  Build out as ya wish.