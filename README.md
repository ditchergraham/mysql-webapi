# Web-API-MySQL
Web API than can be used by an application to connect with MySQL

## Requirments to locally run this API
* MySQL 
* XAMPP
* Git client (Git bash, Fork, etc)

## Guidelines
The login API expects the user to provide an API key.
If no API key is provided? Then a JSON series is returned to the API with the following information:
```JSON
{
    "code": 403,
    "message": "Access denied. There's no API key specified. Please specify an API key."
}
``` 
If an API key is provided but the key can't be foud in the system, then a JSON series is returned with the following information: 
```JSON
{
    "code": 403,
    "message": "Access denied. The specified API key doesn't exists. Please specify a valid API key."
}
```

## Installation
Navigate the code or clone:
https://github.com/ferran1/Web-API-MySQL.git

### In order to succesfully use this API, a MySQL database with the at least the following 2 tables is needed:

```markdown
|-- user
|   |-- email 
|   |-- password
|-- api
|   |-- api_key
|   |-- used_by (optional)
```

### Following, This data is needed in ./models/DatabaseModel.php to connect to the database server
* `./env/database.txt` -> Enter the name of your database.
* `./env/host.txt` -> Enter the name of your host
* `./env/password.txt` -> Enter the password of your database user
* `./env/user.txt` -> Enter the username of your database user

## Usage

### Login
In order to succesfully login from an application, send a POST request with the 3 parameters (email, password, api_key) to the login script.

Correct login API URL if the the project is installed locally using XAMPP (without params):
http://localhost/web-api/Login.php

If the correct api_key, email of the user and the password of the user were specified, the API should return `"code": 200,` in JSON format.

### DatabaseModel
The database model is user to make a connection to the database.
If you want to further expand your application, you can use the CRUD operations in this model. 
