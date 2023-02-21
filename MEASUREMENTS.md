## Measurement API

The functional design is as follows
- you have an exercise with a name and a start (first) and stop time
- you're having a party
- it has 1 or more targets (Ip or domain)
- the type is also linked to a target (web server, dns or smtp)
- 1 or more measurements can be added for a target
- this is done by adding a measurement_api definition to a target measurement
- a measurement_api also knows the type (webserver, dns or smtp)
- a configjson definition is recognized per measure_api and type
- for RIPE this contains the (json code for) definitions, probes and things like start/stop/oneoff

For an exercise

If the exercise with the parties and targets is defined in this way, the measurements must be created in RIPE. This can be done via a plugin console command:

### Measurement API with CLI commands

#### Check if measurmentapi works
```shell
 php artisan ddosgameboard:measurementAPI -h
```

```shell
php artisan ddosgameboard:measurementAPI
```

If you give the -h option, (summarized) help information will be displayed.

When you have set up the Measurements API definitions correctly (see appendix for example configjson code), the measurements can be created in RIPE ALTAS with:
```shell
php artisan ddosgameboard:measurementAPI create
```
This command will:
See if the exercise is in the future
Create the meeting in RIPE ATLAS per parties, per target, per measument definition
(if the measurement already exists for the time period, it will NOT be created twice)

You can find created measurements in the RIPE ATLAS website. And possibly delete it again (DELETE) when only for testing.

During an exercise

A command that allows you to emulate a Cronjob
```shell
php artisan ddosgameboard:measurementAPI measure
```

This command looks at exercise, parties, targets and measurement definitions and converts the measurements in RIPE ATLAS into values in the measurements table.

The response time of all probes measurements is cumulatively collected per minute (average).

### View RIPE ATLAS measurements

With the following command you can track the translation of RIPE measurements to gameboard values per minute:

```shell
php artisan ddosgameboard:measurementAPI show -m <mid>
```

(optional) With <mid> the measurement ID is number in RIPE ATLAS, for example 49131119.
