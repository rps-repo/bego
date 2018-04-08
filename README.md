# Bego

Bego is a library for making DynamoDb queries simpler to work with

## Example ##
```
$client = new Aws\DynamoDb\DynamoDbClient([
    'version' => 'latest',
    'region'  => 'eu-west-1',
    'credentials' => [
        'key'    => 'test',
        'secret' => 'test',
    ],
]);

$time      = strtotime('-24 hours');
$name      = 'Test';
$server    = 'Web-Server-1';
$date      = date('Y-m-d H:i:s', $time);

$query = Bego\Query::create()
    ->table('Logs')
    ->condition('Timestamp', '>=', $date)
    ->filter('Server', '=', $server);

/* Compile all options into one request */
$statement = $query->prepare($client);

/* Execute result and return first page of results */
$results = $statement->fetch(); 

foreach ($results as $item) {
    echo "{$item['Id']}\n";
}
```

## Key condition and filter expressions ##
Multiple key condition / filter expressions can be added. DynamoDb applies key conditions to the query but filters are applied to the query results
```
$results = Bego\Query::create()
    ->table('Logs')
    ->condition('Timestamp', '>=', $date)
    ->condition('Name', '=', $name)
    ->filter('Server', '=', $server)
    ->prepare($client)
    ->fetch(); 

```

## Combining steps into one chain ##
```
$results = Bego\Query::create()
    ->table('Logs')
    ->condition('Timestamp', '>=', $date)
    ->condition('Name', '=', $name)
    ->filter('Server', '=', $server)
    ->prepare($client)
    ->fetch(); 

```

## Descending Order ##
DynamoDb always sorts results by the sort key value in ascending order. Getting results in descending order can be done using the reverse() flag:
```
$statement = Bego\Query::create()
    ->table('Logs')
    ->reverse()
    ->condition('Timestamp', '>=', $date)
    ->condition('Name', '=', $name)
    ->filter('Server', '=', $server)
    ->prepare($client);
```

## Indexes ##
```
$results = Bego\Query::create()
    ->table('Logs')
    ->index('Name-Timestamp-Index')
    ->condition('Timestamp', '>=', $date)
    ->condition('Name', '=', $name)
    ->filter('Server', '=', $server)
    ->prepare($client)
    ->fetch();
```

## Paginating ##
DynanmoDb limits the results to 1MB. Therefor, pagination has to be implemented to traverse beyond the first page. There are two options available to do the pagination work: fetchAll() or fetchMany()
```
$statement = Bego\Query::create()
    ->table('Logs')
    ->condition('Timestamp', '>=', $date)
    ->condition('Name', '=', $name)
    ->filter('Server', '=', $server)
    ->prepare($client);

/* Get all items no matter the cost */
$results = $statement->fetchAll();

/* Execute as many calls as is required to get 1000 items */
$results = $statement->fetchMany(1000); 
```