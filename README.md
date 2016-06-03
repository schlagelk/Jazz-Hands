# Jazz-Hands
Uses Slim 3, Laravel's Eloquent ORM, Bootstrap to provide a starting application which supports user authentication and email confirmation.

Make a copy of app/bootstrap/.env.sample into a file called .env and set your database credentials in the first section, your SMTP mail settings in the second (this uses the PHPMailer package) and your base url in the third.

These values are loaded into bootstrap/app.php where you see getenv('xxx')
