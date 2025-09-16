# DDoS Gameboard API documentatie

Basis for the API is: ```https://<domain>/api/v1```


## OUATH2

For security purposes you can only communicate with the api through OAuth2, we use Laravel Passport ta handle this for us
It Should already be installed when finishing the Install.md by Composer

### Setup

#### Install passport
To setup Laravel Passport before using the api make sure you run the following commands on the server in the CLI:
```shell
php artisan install
php artisan passport:client --password

```

#### Install a user and give it sole permission to handle the api

As a logged in SuperUser in the winterCMS go to the settings->administrators: (/backend/backend/users)

create a new user:
```yaml
Login: api
First name: Api
Last Name: SystemOnly
Password: <A Strong Password Off Course>
```

Now go to permissions tab and deny all permissions but allow the permission "Access API calls", or give the user the DDOS Gameboard API User role.

### Auth and get Access Token
POST:```/api/authentication```

BODY:
```json
{
    "login": {api},
    "password": {That great password from earlier},
}
```

This call will log the status of a target to the ddosgameboard, the log text is returned to the callee when succesful

RETURNS
```json
{
    "token_type": "Bearer",
    "expires_in": {time},
    "access_token": {hash},
    "refresh_token": {hash},
}
```

### Refresh Access token
POST:```/api/authentication/refresh```

BODY:
```json
{
    "refresh_token": {hash},
}
```

This Call return a new access_token and refresh_token for when your access_token has expired

RETURNS
```json
{
    "token_type": "Bearer",
    "expires_in": {time},
    "access_token": {hash},
    "refresh_token": {hash},
}
```

## Miscellaneous
### Log status of a Target
POST:```/api/v1/target/{id}/state/{state}```

where {state} 0 is down and {state} 1 is up

This will log a prefixed message in the Quicklogging and Log in the gameboard about the status
The user who logs will be refered as "System" and has user_id 0 in the database

RETURNS (transaction)
```json
{

    "succes": "Target: acme.domain.nl status is: down"
}
```


### Log as system user directly in the Gameboard

When supplying the correct Bearer Acces token you can log straight into the gamboard quicklog and log with

POST:```/api/log```

x-www-form-urlencoded (encoded as JSON Post form data)
```json
{
  "log": "{custom log text}",
  "timestamp": "14:51:44"
}
```


## ddostests

| method | URI             | description |
| ------ | --------------- | ----------- |
| GET    | /ddostests      | Gets all DDoS tests |

<!-- NOT YET BUILD
| GET    | /ddostests/{id} | Gets values of specified DDoS test |
| GET    | /ddostests/{id}/targets | Gets values targets of specified DDoS test |
| POST   | /ddostest       | Create a new DDoS test with specified values |
| PUT    | /ddostest/{id}  | Updates the specified DDoS test |
| DELETE | /ddostest/{id}  | Deletes the specified DDoS test |
-->

JSON:

```
GET api/v1/ddostests
[
    {
        "id": 1,
        "start": epoch timestamp,
        "end": epoch timestamp"
        "active": boolean,
        "updated_at": timestamp
    },
     ...
]
```

<!-- NOT YET BUILD
```
GET api/v1/ddostests/{id}
{
    "start": epoch timestamp,
    "end": epoch timestamp
    "active": boolean,
    "updated_at": timestamp
}
```


Same for PUT, POST and DETELE. With POST, {id} is not given, since it is a new record.

In toekomst kunnen meerdere DDoS oefeningen geconfigureerd worden.
Onder ddostest het veld activated. None voor geen actieve ddos test en anders het nummer. Voor nu maar 1 ddos test in te stellen en dus altijd nummer 1. Tevens veld activated_time. De Fireball kan dan met de activated en de activated_time bepalen of hij de actieve configuratie al heeft of nog moet ophalen.


```
GET api/v1/ddostests/{id}/targets

Same values as with GET api/v1/targets, but only with the target {id}'s for this DDoS test
```

-->

## Targets

| method | URI           | description |
| ------ | ------------- | ----------- |
| GET    | /targets      | Gets all targets |

<!-- NOT YET BUILD

| GET    | /targets/{id} | Gets values of specified target |
| GET    | /targets/{id}/measurementtype | Gets values of measurement type of specified target |
| POST   | /targets       | Create a new target with specified values |
| PUT    | /targets/{id}  | Updates the specified target |
| DELETE | /targets/{id}  | Deletes the specified target |
-->
A target in this list has only one measurement types. Targets with multiple measurement types need to be added multiple times.


JSON:

```
GET api/v1/targets
[
   {
      "id": 1,
      "meassurementtype": 1,
      "value": "s3group.nl"
   },
   ...
]
```
<!-- NOT YET BUILD
Value can be an IPv4 or IPv6 IP address, a domain name or an URL

```
GET api/v1/targets/{ID}
{
    "meassurementtype": 1,
    "value": "s3group.nl"
}
```

Same for PUT, POST and DETELE. With POST, {id} is not given, since it is a new record.

```
GET api/v1/targets/{ID}/measurementtype

Same values as with GET api/v1/measurementtypes/{id}
```
-->

## Measurementtypes

| method | URI             | description |
| ------ | --------------- | ----------- |
| GET    | /measurementtypes      | Get values of all measurementtypes |
| GET    | /measurementtypes/{id} | Get values of the specified measurementtype   |

<!-- NOT YET BUILD
| GET    | /measurementtypes/{id}/nodelist | Get values of nodelist of the specified measurementtype   |
| POST   | /measurementtypes       | Create a new measurementtype |
| PUT    | /measurementtypes/{id}  | Update the specified measurementtype |
| DELETE | /measurementtypes/{id}  | Delete the specified measurementtype, no data |

-->
JSON:

```
GET api/v1/measurementtypes
[
   {
      "id": 1,
      "name": "ping",
      "nodelist": 1,

   },
   ...
]
```

```
GET api/v1/measurementtypes/{id}
{
    "name": "ping",
    "nodelist": 1,
    "allnodes": 0
}
```

<!-- NOT YET BUILD
Same for PUT, POST and DETELE. With POST, {id} is not given, since it is a new record.

```
GET api/v1/measurementtypes/{id}/nodelist

Same values as with GET api/v1//nodelists/{id}
```

 -->

measurement types are: ping, http, smtp, dns, traceroute


## nodelists

| method | URI             | description |
| ------ | --------------- | ----------- |
| GET    | /nodelists      | Get values of all nodelists |
| GET    | /nodelists/{id} | Get values of the specified nodelist |
<!-- NOT YET BUILD
| POST   | /nodelists      | Create a new nodelist in the list of nodelists |
| PUT    | /nodelists/{id} | Update the specified nodelist |
| DELETE | /nodelists/{id} | Delete the specified nodelist, no data |
-->

JSON:

```
GET api/v1/nodelists
   {
      "1":  [
              "nrn-nl.ark",
              "ams3-nl.ark",
              "ens-nl.ark"
      ],
      ...
   }

   ```

   (edited)

```
GET api/v1/nodelists/{id}
{
   [
       "nrn-nl.ark",
       "ams3-nl.ark",
       "ens-nl.ark"
   ]
}
```

<!-- NOT YET BUILD
Same for PUT, POST and DETELE. With POST, {id} is not given, since it is a new record.


Conventie dat nodelists id=1 de lijst is met alle actieve nodes, vanuit de meetomgeving. Andere nummers zijn lijsten die bij measurementtypes gebruikt worden.

-->
