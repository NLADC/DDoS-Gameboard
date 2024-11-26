# DDoS Gameboard API documentatie
Basis voor de API is: https/domain/api/v1

## OUATH2
### Setup
To setup Laravel Passport before using trhe api make sure you run the following commands on the server in the CLI:
```shell
php artisan install
php artisan passport:client --password

```

### Auth and Get Access Token
POST:```/api/authentication```

BODY:
```json
{
    "login": {username},
    "password": {string},
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
    "headers": {},
    "original": {
        "result": true,
        "message": "",
        "log": "{\"user_id\":0,\"log\":\"Target: www.something.nl status is: up\",\"timestamp\":\"2024-09-12 17:10:05\",\"id\":1623,\"user\":{\"id\":0,\"partyId\":0,\"name\":\"System\",\"role\":\"\",\"settings\":\"\",\"party\":{\"id\":0,\"deleted_at\":null,\"name\":\"No party\",\"0\":\"logo\",\"1\":\"\"}},\"partyId\":0}"
    },
    "exception": null
}
```


### Log as system user directly in the Gameboard
POST:```/api/log```

x-www-form-urlencoded (encoded as JSON Post form data)
```json
{
  "log": "{custom log text}",
  "timestamp": "14:51:44"
}
```

When supplying the correct Bearer Acces token you can log straight into the gamboard quicklog and log

RETURNS (transaction)
```json
{
    "result": true,
    "message": "",
    "log": "{\"user_id\":0,\"log\":\"{logtext},\"timestamp\":\"2024-09-12 14:51:44\",\"id\":1586,\"user\":{\"id\":0,\"partyId\":0,\"name\":\"System\",\"role\":\"\",\"settings\":\"\",\"party\":{\"id\":0,\"deleted_at\":null,\"name\":\"No party\",\"0\":\"logo\",\"1\":\"\"}},\"partyId\":0}"
}
```

## ddostests

| method | URI             | description |
| ------ | --------------- | ----------- |
| GET    | /ddostests      | Gets all DDoS tests                          |
| GET    | /ddostests/{id} | Gets values of specified DDoS test           |
| POST   | /ddostest       | Create a new DDoS test with specified values |
| PUT    | /ddostest/{id}  | Updates the specified DDoS test              |
| DELETE | /ddostest/{id}  | Deletes the specified DDoS test              |

JSON

GET all

```
[
    {
        "id": 1,
        "start": epoch timestamp,
        "end": epoch timestamp"
        "activated": boolean,
        "updated_at": timestamp
    },
     ...
]
```

GET {id}, POST, PUT {id}

```
{
    "id": 1,
    "start": epoch timestamp,
    "end": epoch timestamp
    "active": boolean,
    "updated_at": timestamp
}
```



In toekomst kunnen meerdere DDoS oefeningen geconfigureerd worden.
Onder ddostest het veld activated. None voor geen actieve ddos test en anders het nummer. Voor nu maar 1 ddos test in te stellen en dus altijd nummer 1. Tevens veld activated_time. De Fireball kan dan met de activated en de activated_time bepalen of hij de actieve configuratie al heeft of nog moet ophalen.


```/ddostest/{id}/targets```

Gelijk aan /targets, maar dan alleen die voor die test gebruikt worden


## targets

| method | URI           | description |
| ------ | ------------- | ----------- |
| GET    | /targets      | Gets all targets |
| GET    | /targets/{id} | Gets values of specified target |
| GET    | /targets/{id}/measurementtype | Gets values of measurement type of specified target |
| POST   | /targets       | Create a new target with specified values |
| PUT    | /targets/{id}  | Updates the specified target |
| DELETE | /targets/{id}  | Deletes the specified target |

Vraag is of in de lijst bij een target meerdere measurementtypes kunnen gelden of dat het altijd een een-op-een relatie is. Dan kan measurementtype een veld zijn van targets. Als meerdere mogelijk zijn dan een list. Er wordt uitgegaan van 1 measurementtype bij een target record.


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


## measurementtypes

| method | URI             | description |
| ------ | --------------- | ----------- |
| GET    | /measurementtypes      | Get values of all measurementtypes |
| GET    | /measurementtypes/{id} | Get values of the specified measurementtype   |
| GET    | /measurementtypes/{id}/nodelist | Get values of nodelist of the specified measurementtype   |
| POST   | /measurementtypes       | Create a new measurementtype |
| PUT    | /measurementtypes/{id}  | Update the specified measurementtype |
| DELETE | /measurementtypes/{id}  | Delete the specified measurementtype, no data |

```
GET api/v1/measurementtypes
[
   {
      "id": 1,
      "meassurementtype": "ping",
      "nodelist": 1
   },
   ...
]
```

```
GET api/v1/measurementtypes/{id}
{
    "meassurementtype": "ping",
    "nodelist": 1
}
```

Same for PUT, POST and DETELE. With POST, {id} is not given, since it is a new record.

```
GET api/v1/measurementtypes/{id}/nodelist

Same values as with GET api/v1//nodelists/{id}
```


measurement types are: ping, http, smtp, dns, traceroute

NB: is this list ok, complete? Differentiate http and https and GET and POSTS?

## nodelists

| method | URI             | description |
| ------ | --------------- | ----------- |
| GET    | /nodelists      | Get values of all nodelists            |
| GET    | /nodelists/{id} | Get values of the specified nodelist   |
| POST   | /nodelist       | Create a new nodelist                  |
| PUT    | /nodelist/{id}  | Update the specified nodelist          |
| DELETE | /nodelist/{id}  | Delete the specified nodelist, no data |


JSON

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

Same for PUT, POST and DETELE. With POST, {id} is not given, since it is a new record.


Conventie dat nodelists id=1 de lijst is met alle actieve nodes, vanuit de meetomgeving. Andere nummers zijn lijsten die bij measurementtypes gebruikt worden.


NB: als bij een measurementtype de wens is om alle nodes te gebruiken dan niet nodelist 1 gebruiken, maar bij measurementtype iets als een veld allnodes = true. Dan kan de Fireball namelijk zelf aangeven dat alle nodes gebruikt moeten worden ipv een lijst. Daar zit verschil in.
