# Mysqli helpers

## Русский
Подробное описание можно найти в docblock комментариях исходного кода

## Usage
```php
$db = new mysqli(/**/);
$store = new Store($db);

$store->insert('table', ['column' => 'value']);
$store->update('table', ['id' => $id, 'column' => 'value']);

$store->getById('table', $id);
$store->getByColumn('table', 'some_id', $id);
$store->deleteById('table', $id);

$store->getLast('table');
$store->getLastN('table', $n);

$store->search('table',['column' => 'regex search string', 'anotherColumn' => 'search']);
```
