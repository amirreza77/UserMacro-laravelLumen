# UserMacro-laravelLumen
This is in depended system for user management by lumen Laravel . this system designed for both verification email and SMS.
In the first step user could register and then you could decide which approach is appropriate for you to verify her/him 

## Instalation

### clone repo and unzip it then in terminal do this syntaxes 
`composer install`

`php artisan migrate`

********************************************************
### Do Not Forget To

 change your env file based on your database and email server config
 
********************************************************

first install lumen of use this Repo have fun!

# Endpoints 

all request using of Endpoints

# Workflow 

# 1 - Add User 
simply add user in the first step by calling /user/add with belo value

```sh
name
username
password
cellphone
address
avatar -> as file
email
```
# Example
```php
<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://localhost:8000/user/add/',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => array(
  'name' => 'amirreza',
  'username' => 'admin',
  'password' => '123456',
  'cellphone' => '0912xxxxxx',
  'address' => 'YourAdd',
  'avatar' => '',
  'email' => 'youremail@gmail.com'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
?>
```
# 2 - Send Verification Code to User
   ##  2-1 Send Verification Code to User by sending SMS
  you could verify user by sending verification code SMS directly to her/him cellphone and get token for login system 

### API Reference

#### POST all items

```http
  POST /user/validatebycell/
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `cellphone` | `string` | **Required**             |

### Usage/Examples

```php
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://localhost:8000/user/validatebycell/',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => array('cellphone' => '09xxxxxxxxxx'),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
```
#### Please fill Your SMS config in .env file and change SMS sending api in the file app\Http\Controllers in sendSms function line:141  
### Response : '200' means your verification code is sent

##  2-2 Send Verification Code to User by sending EMAIL
Fist you should send username and password and system will send verification email

```http
  POST /user/lwup
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `username` | `string` | **Required**             |
| `password` | `string` | **Required**             |



### Usage/Examples
```php
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://localhost:8000/user/lwup',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => array('username' => 'admin','password' => '123456'),
));
$response = curl_exec($curl);
curl_close($curl);

```

# 3- Verify User
verify user useable in cellphone verification method. after verify by email or cellphone system generate token code for cheking user

## EndPoint

```http
  POST /user/lwup
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `cellphone` | `string` | **Required**             |
| `vcode` | `string` | **Required** you got this code from sms             |

### Usage/Examples
```php
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://localhost:8000/user/validatecode/',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => array('cellphone' => '0912xxxxxx','vcode' => 'xxxx'),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

```
 ## Response:

 | Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `status` | `string` |  200 or 400           |
| `msg` | `string` |            |
| `token` | `string` |   unique code for user    |

### we will use `Token` for cheking user login and if it is verify so you should store token in your program. 
#### you could change token availability time in `.env` file default is 15 minutes and it will be refresh if user get token diuring 15 minutes  
 
# 4- Check User is login

you got token from previuse steps right?

## Endpoint

```http
  POST /user/userlogin/
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `token` | `string` | **Required**             |

### Usage/Examples
```php
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://localhost:8000/user/userlogin/',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => array('token' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

```

## Response:

 | Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `status` | `string` |  200 or 400           |
| `msg` | `string` |            |


# 4- Show User

you got token from previuse steps right?

## Endpoint

```http
  POST /user/show
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `token` | `string` | **Required**             |

### Usage/Examples
```php
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://localhost:8000/user/show',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => array('token' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

```
## Response:

you will get all user details as Responce


# 5- Edit User

you got token from previuse steps right?

## Endpoint

```http
  POST /user/edit
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `name` | `string` | **Required**             |
| `username` | `string` | **Required**             |
| `password` | `string` | **Required**             |
| `address` | `string` | **Required**             |
| `avatar` | `string` | **Required** as file             |
| `email` | `string` | **Required**              |
| `token` | `string` | **Required**             |

#### if you dont want to change one of them send back it again without changing

### Usage/Examples
```php
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://localhost:8000/user/edit/',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => array('name' => 'YOURNAME',
  'username' => 'admin',
  'password' => '123456',
  'address' => 'YOURADD',
  'avatar'=> new CURLFILE('/path/to/file'),
  'email' => 'youremail@DOMAIN.com',
  'token' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
```
## Response:

 | Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `status` | `string` |  200 or 400           |
| `msg` | `string` |            |

# Finaly fill free to download PostMan WithName `User Api.postman_collection.json` From Root Of This Repo ! 


## License

The Lumen framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


