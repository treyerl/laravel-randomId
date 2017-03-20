# Laravel Eloquent Models with random ID
Traits to quickly add the capability to generate random IDs for your laravel eloquent models.

Contained traits:

* __RandomIntId__ - _set your database column type to `bigInt` for IDs >= 10 digits_
* __RandomBinId__ - _Laravel's Schema class typically translates `$table->binary()` to `blob`. Use raw queries for databases like MySQL where a `binary(LENGTH)` type is supported._
* __RandomUuid__ - _Randomly generated UUID; corresponds to UUID v4; sets the relevant bits accordingly_

###Usage:
Copy the needed traits to your app folder.

__RandomIntId:__
```php
namespace App;

use Illuminate\Database\Eloquent\Model;

class MyModel extends Model
{
	use RandomIntId;

    public $incrementing = false;

	protected $guarded = ['id'];

	protected function getIdLength(){
		// defaults to 12; make sure the id column is set to bigInt for >= 10 digits
		return 16;
	}
}
```
__RandomBinId:__
```php
namespace App;

use Illuminate\Database\Eloquent\Model;

class MyModel extends Model
{
	use RandomBinId;

    public $incrementing = false;

	protected $guarded = ['id'];

	protected function getIdLength(){
		// defaults to 16
		return 8;
	}
}
```

__RandomUuid:__
```php
namespace App;

use Illuminate\Database\Eloquent\Model;

class MyModel extends Model
{
	use RandomUuid;

    public $incrementing = false;

	protected $guarded = ['id'];

	protected function getIdLength(){
		// defaults to 16
		return 8;
	}
}
```

__RandomBinId with custom representation:__
Overriding `getIdRepresentation()` and `getIdFromRepresentation()` allows for customization of the string representation of the binary id. By default its encoding is hexadecimal. Here we make use of a Base32 encoder `composer require christian-riesen/base32`, which in contrast to Base64 allows to use the encoded id in URLs.

```php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Base32\Base32;

class MyModel extends Model
{
	use RandomBinId;

    public $incrementing = false;

	protected $guarded = ['id'];

	protected function getIdLength(){
		return 8;
	}

	public function getIdRepresentation(){
		return implode(".", str_split(str_replace("=", "", Base32::encode($this->getKey())), 3));
	}

	public static function getIdFromRepresentation($representation){
		return Base32::decode(str_pad(str_replace(".", "", $representation), 16, "="));
	}
}
```

### Considerations
The provided traits create a random id in PHP, check if it exists in the database, repeat those two steps until a unique id is found. This procedure requires an atomic database access, which is implemented very easily with Laravel: `DB::transaction(function(){your database calls})`.

Nevertheless, this implementation CAN be problematic for large data sets. As the database is blocked in an atomic call while the client potentially needs to repeat a randomID generation many times, this implementation requires you to choose the number of digits / bytes of your IDs wisely. Check out the table on the [Wikipedia page on the "Birthday attack"](https://en.wikipedia.org/wiki/Birthday_attack#Mathematics) to get an idea.

If you need maximal performance consider generating the IDs beforehand and store them to a separate table, from which you can fetch and delete one at a time, if you need a new one.
