# Web-API-MySQL
Web API than can be used by an application to connect with MySQL

## Requirments to locally run this API
* MySQL 
* XAMPP
* Git client (Git bash, Fork, etc)

## Guidelines
The login API expects the user to provide an API key.
If you don't provide an API key? Then a JSON series is returned to the API with the following information:
```JSON
{
    "code": 403,
    "message": "Access denied. There's no API key specified. Please specify an API key."
}
``` 
If you do provide an API key but the key is not in the database, then a JSON series is returned with the following information: 
```JSON
{
    "code": 403,
    "message": "Access denied. The specified API key doesn't exists. Please specify a valid API key."
}
```

## Installation
Navigate the code or clone:
https://github.com/ferran1/Web-API-MySQL.git

### In order to succesfully use this API, you'll need a MySQL database with the at least the following 2 tables:

```markdown
|-- user
|   |-- email 
|   |-- password
|-- api
|   |-- api_key
|   |-- used_by (optional)
```

### Following, you'll need to specify your MySQL details in the env folder
* `./env/database.txt` -> Enter the name of your database.
* `./env/host.txt` -> Enter the name of your host
* `./env/password.txt` -> Enter the password of your database user
* `./env/user.txt` -> Enter the username of your database user

## Usage

### Login
In order to succesfully login from an application, you will have to send a POST request with the 3 parameters (email, password, api_key) to the login script.

If you installed the project locally using xampp, do a POST request to the following URL (with params):
http://localhost/web-api/Login.php

If you specified the correct api_key, email of the user and the password of the user, the API should return `"code": 401,` in JSON format.
