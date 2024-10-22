# PHPFUI\ORM [![Tests](https://github.com/phpfui/ORM/actions/workflows/tests.yml/badge.svg)](https://github.com/phpfui/ORM/actions?query=workflow%3Atests) [![Latest Packagist release](https://img.shields.io/packagist/v/phpfui/ORM.svg)](https://packagist.org/packages/phpfui/ORM) ![](https://img.shields.io/badge/PHPStan-level%206-brightgreen.svg?style=flat)

### PHPFUI\ORM a minimal Object Relational Mapper (ORM) for MySQL, MariaDB and SQLite3
Why another PHP ORM? In writing minimal and fast websites, it was determined that existing PHP ORM solutions were overly complex. **PHPFUI\ORM** is a little more than 6.7K lines of code in 50 files.  It is designed to have a minimal memory footprint and excellent execution times for most database needs.

Performance [comparison of PHPFUI\ORM to Eloquent](https://github.com/phpfui/php-orm-sql-benchmarks) for different SQL implementations.

**PHPFUI\ORM** is not an attempt to write an abstraction around SQL as other ORMs do, rather it is a way to work with SQL that closely matches the semantics of SQL, with the power of PHP objects.  It allows PHP to manipulate SQL queries without having to write SQL in plain text. This is very useful for queries generated via user interfaces where the user is given a lot of flexability in how a query is defined.

## Features
- **Active Records** A fully type checked object interface and implement basic CRUD functionality.
- **Active Tables** Full table operations (select, update, insert and delete) including support for where, having, limits, ordering, grouping, joins and unions.
- **Data Cursors** Cursors implement **iterable** and **Countable** eliminating the need to read full arrays into memory.
- **Validation** Fully customizable and translatable backend validation.
- **Virtual Fields** Supports get and set semantics for any custom or calculated field such as Carbon dates.
- **Migrations** Simple migrations offer atomic up and down migrations.
- **Relations** Parent, children, one to one, many to many, and custom relationships.
- **Transactions** Object based transactions meaning exceptions will not leave an open transacton.
- **Type Safe** Prevents stupid type errors.
- **Injection Safe** Uses PDO placeholders and field sanitation to prevent injection attacks.
- **Raw SQL Query Support** Execute any valid SQL command.
- **Multiple Database Support** Work with multiple databases simultaneously.
- **Multi-Vendor Support** Built on PDO with support for MySQL, MariaDB and SQLite.

## Usage
### Setup
```php
$pdo = new \PHPFUI\ORM\PDOInstance($yourConnectionString);
// perform any custom configuration settings needed on $pdo
\PHPFUI\ORM::addConnection($pdo);
```

### Active Record Example
```php
$book = new \App\Record\Book();
$book->title = 'PHP ORM: The Right Way';
$book->price = 24.99;

$author = new \App\Record\Author();
$author->name = 'Bruce Wells';

$book->author = $author;  // Save the author
$book->save();            // Save the book
```

### Active Table Example
```php
$bookTable = new \App\Table\Book();
$bookTable->setWhere(new \PHPFUI\ORM\Condition('title', '%orm%', new \PHPFUI\ORM\Operator\Like()));
$bookTable->join('author');

foreach ($bookTable->getDataObjectCursor() as $book)
  {
  echo "{$book->title} by {$book->name} is $ {$book->price}\n";
  }

// discount all PHP books to 19.99
$bookTableUpdater = new \App\Table\Book();
$bookTableUpdater->setWhere(new \PHPFUI\ORM\Condition('title', '%PHP%', new \PHPFUI\ORM\Operator\Like()));
$bookTableUpdater->update(['price' => 19.99]);

foreach ($bookTableUpdater->getRecordCursor() as $book)
  {
  echo "{$book->title} by {$book->author->name} is now $ {$book->price}\n";
  }
```

### Validation Example
```php
$book->title = 'This title is way to long for the database and will return a validation error. We should write a migration to make it varchar(255)!';
$errors = $book->validate();
foreach ($errors as $field => $fieldErrors)
  {
  echo "Field {$field} has the following errors:\n";
  foreach ($fieldErrors as $error)
    {
    echo $error . "\n";
    }
  }
```

### Migration Example
Migrations are atomic and can be run in groups or individually up or down.

```php
namespace App\Migration;

class Migration_1 extends \PHPFUI\ORM\Migration
  {

  public function description() : string
    {
    return 'Lengthen book.title field to 255';
    }

  public function up() : bool
    {
    return $this->alterColumn('book', 'title', 'varchar(255) not null');
    }

  public function down() : bool
    {
    return $this->alterColumn('book', 'title', 'varchar(50) not null');
    }
  }
```

## Type Safety
### Exceptions Supported
Exceptions are generated in the following conditions:
- Accessing field or offset that does not exist
- Deleting records without a where condition (can be overridden)
- Incorrect type for Operator (must be an array for **IN** for example)
- Passing an incorrect type as a primary key
- Invalid join type
- Joining on an invalid table

All of the above exceptions are programmer errors and strictly enforced. Empty queries are not considered errors. SQL may also return [Exceptions](https://www.php.net/manual/en/class.exception.php) if invalid fields are used.

### Type Conversions
If you set a field to the wrong type, the library logs a warning then converts the type via the appropriate PHP cast.

## Multiple Database Support
While this is primarily a single database ORM, you can switch databases at run time. Save the value from `$connectionId = \PHPFUI\ORM::addConnection($pdo);` and then call `\PHPFUI\ORM::useConnection($db);` to switch.  `\PHPFUI\ORM::addConnection` will set the current connection.

The programmer must make sure the proper database is currently selected when database reads or writes happen and that any primary keys are correctly handled.

### Copy tables example:
```php
// get the current connection just for fun
$currentConnection = \PHPFUI\ORM::getConnection();

$cursors = [];
// getRecordCursor will bind the cursor to the current database instance
$cursors[] = (new \App\Table\Author())->getRecordCursor();
$cursors[] = (new \App\Table\Book())->getRecordCursor();

// set up a new database connection
$pdo = new \PDO($newConnectionString);
$newConnectionId = \PHPFUI\ORM::addConnection($pdo);

foreach ($cursors as $cursor)
  {
  foreach ($cursor as $record)
    {
    $record->insert();	// insert into new database ($newConnectionId)
    }
  }
// back to the original database
\PHPFUI\ORM::useConnection($currentConnection);
```

## Documentation
+ [Setup](<https://github.com/phpfui/ORM/blob/main/docs/1. Setup.md>)
+ [Active Record](<https://github.com/phpfui/ORM/blob/main/docs/2. Active Record.md>)
+ [Active Table](<https://github.com/phpfui/ORM/blob/main/docs/3. Active Table.md>)
+ [Cursors](<https://github.com/phpfui/ORM/blob/main/docs/4. Cursors.md>)
+ [Virtual Fields](<https://github.com/phpfui/ORM/blob/main/docs/5. Virtual Fields.md>)
+ [Migrations](<https://github.com/phpfui/ORM/blob/main/docs/6. Migrations.md>)
+ [Validation](<https://github.com/phpfui/ORM/blob/main/docs/7. Validation.md>)
+ [Translations](<https://github.com/phpfui/ORM/blob/main/docs/8. Translations.md>)
+ [Transactions](<https://github.com/phpfui/ORM/blob/main/docs/9. Transactions.md>)
+ [Miscellaneous](<https://github.com/phpfui/ORM/blob/main/docs/10. Miscellaneous.md>)

## Full Class Documentation
[PHPFUI/ORM](http://phpfui.com/?n=PHPFUI\ORM)

## License
PHPFUI is distributed under the MIT License.

## PHP Versions
This library only supports **modern** versions of PHP which still receive security updates. While we would love to support PHP from the late Ming Dynasty, the advantages of modern PHP versions far out weigh quaint notions of backward compatibility. Time to upgrade.
