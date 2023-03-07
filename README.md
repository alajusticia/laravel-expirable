# Laravel Expirable

Inspired by the SoftDeletes trait, this package provides a trait to make Eloquent models expirable.

It relies on an additional attribute (named `expires_at` by default) that contains the date of expiration
(or `null` to make the model eternal).

When the expiration date is reached, the model will automatically disappear from all the Eloquent query results
(but still remain in the database).

* [Compatibility](#compatibility)
* [Installation](#installation)
  * [Prepare your model](#prepare-your-model)
    * [Change the default name of the expiration attribute](#change-the-default-name-of-the-expiration-attribute)
    * [Set a default period of validity](#set-a-default-period-of-validity)
  * [Prepare your migration](#prepare-your-migration)
* [Usage](#usage)
  * [Retrieving models](#retrieving-models)
    * [Retrieving valid models](#retrieving-valid-models)
    * [Retrieving all models](#retrieving-all-models)
    * [Retrieving only expired models](#retrieving-only-expired-models)
    * [Retrieving only eternal models](#retrieving-only-eternal-models)
    * [Retrieving expired models since](#retrieving-expired-models-since)
  * [Get the expiration date](#get-the-expiration-date)
  * [Set the expiration date manually](#set-the-expiration-date-manually)
    * [The basic way](#the-basic-way)
    * [Using expiresAt()](#using-expiresat)
    * [Using lifetime()](#using-lifetime)
  * [Make existing models expire](#make-existing-models-expire)
    * [Expire models by key](#expire-models-by-key)
    * [Expire models by query](#expire-models-by-query)
  * [Revive expired models](#revive-expired-models)
  * [Make existing models eternal](#make-existing-models-eternal)
  * [Extend model lifetime](#extend-model-lifetime)
  * [Shorten model lifetime](#shorten-model-lifetime)
  * [Reset the expiration date to default](#reset-the-expiration-date-to-default)
  * [Get the status of a model](#get-the-status-of-a-model)
  * [Purge expired records](#purge-expired-records)
* [License](#license)

## Compatibility

You're reading the documentation for the v1 of this package.
This version supports **Laravel 5.8, 6, 7, 8 and 9**.

For Laravel 10 support, [go to v2](https://github.com/alajusticia/laravel-expirable).

## Installation

Install the package via composer using this command:

```bash
composer require alajusticia/laravel-expirable
```

You can publish the configuration file with:

```bash
php artisan vendor:publish --provider="ALajusticia\Expirable\ExpirableServiceProvider"
```

### Prepare your model

To make a model expirable, add the Expirable trait provided by this package:

```php
use ALajusticia\Expirable\Traits\Expirable;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use Expirable;
```

This trait will automatically add the expiration attribute in the list of attributes that should be mutated to dates.

#### Default name of the expiration attribute

By default the package adds an attribute named `expires_at` on your model.
You can change this name by setting the `EXPIRES_AT` constant (don't forget to set the same name for the column in
the migration, [see below](#prepare-your-migration)).

For example, let's say that we have a `Subscription` model and we want the attribute to be `ends_at`:

```php
use ALajusticia\Expirable\Traits\Expirable;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use Expirable;
    
    const EXPIRES_AT = 'ends_at';
```

You can also change the attribute name globally for all your expirable models by using the `attribute_name` option in
the `expirable.php` configuration file (the constant prevails).
If you do change the name globally in the configuration file, you don't have to set the name in the migration as it will be populated automatically.

#### Set a default period of validity

You can set a default period of validity with the `defaultExpiresAt` public static function.

This method must return a date object or `null`. This way, on saving the model the date of expiration will be automatically added unless you
explicitly provide a date.

An example to set a default period of validity of six months:

```php
use ALajusticia\Expirable\Traits\Expirable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use Expirable;
    
    // ...

    public static function defaultExpiresAt()
    {
        return Carbon::now()->addMonths(6);
    }
```

 ```php
 // Create a new subscription which will expire in six months (using default expiration date)
 $subscription = new Subscription;
 $subscription->save();
 
 // Create a new subscription which will expire in one year (overwrite the default expiration date)
  $subscription = new Subscription;
  $subscription->expiresAt(Carbon::now()->addYear());
  $subscription->save();
 ```

### Prepare your migration

The package requires that you add the expirable column in your migration.
For convenience, the package provides the `expirable()` and `dropExpirable()` blueprint macros ready to use in your migration files:

```php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExpirableColumnToSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->expirable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropExpirable();
        });
    }
```

By default the name of the database column, like the model attribute, will be `expires_at` or the one in the configuration file.
If you modified the default name of the attribute on your model with the `EXPIRES_AT` constant, you need to set the same custom name for the column
in your migration, by giving the macro a parameter with the name.

To continue with our subscription example:

```php
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->expirable('ends_at');
        });
    }

    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropExpirable('ends_at');
        });
    }
```

## Usage

### Retrieving models

#### Retrieving valid models

Do your stuff as usual.
By default, when the date of expiration is reached, the model will automatically be excluded of the results.

For example:

```php
// Get only valid and current subscriptions and exclude expired ones
$subscriptions = Subscription::all();
```

This, and all the next examples, work as well with relationships:

```php
$user = App\User::find(1);
$subscriptions = $user->subscriptions();
```

#### Retrieving all models

To disable the default filtering and retrieve all the models, ignoring their expiration date:

```php
// Get all subscriptions
$subscriptions = Subscription::withExpired()->get();
```

#### Retrieving only expired models

Use the onlyExpired scope:

```php
// Get expired subscriptions
$subscriptions = Subscription::onlyExpired()->get();
```

#### Retrieving only eternal models

To get only the models which do not expire (with expiration date attribute to `null`), use the onlyEternal scope:

```php
// Get unlimited subscriptions
$subscriptions = Subscription::onlyEternal()->get();
```

#### Retrieving expired models since

This package provides a query scope to retrieve only models that have expired for at least a given period of time.
Use the `expiredSince` scope and give it a parameter representing the desired period of time.

The parameter must be a string and the syntax is the same as the syntax accepted by the Carbon `sub` method
(see documentation here: [https://carbon.nesbot.com/docs/#api-addsub](https://carbon.nesbot.com/docs/#api-addsub)).

For example, let's say that you want to definitively delete from the database the models expired since at least one year,
the query will be:

```php
// Delete expired models since one year or more
Subscription::expiredSince('1 year')->delete();
```

### Get the expiration date

To get the expiration date without having to know the name of its attribute, use the getExpirationDate method:

```php
$subscription->getExpirationDate(); // Returns a date object
```

### Set the expiration date manually

#### The basic way

If you know the name of the expiration date attribute, you can simply populate this attribute
with a date object (or `null` for eternal):

```php
// Create a new subscription valid for one month
$subscription = new Subscription();
$subscription->ends_at = Carbon::now()->addMonth();
$subscription->save();
```

Of course it also works with mass assignment, but don't forget to add the attribute you intend to mass assign
(here `ends_at`) in the `$fillable` property of your model:

```php
// Create a new subscription valid for one month
$subscription = Subscription::create([
    'plan' => 'premium',
    'ends_at' => Carbon::now()->addMonth(),
]);
```

#### Using expiresAt()

Use the `expiresAt` method with a date object in parameter (or `null` for eternal) to set an expiration date manually.
On an Eloquent query the changes will be directly saved in the database. On a single model you still need to use the
`save` method:

```php
// Create a new subscription valid for one month
$subscription = new Subscription();
$subscription->expiresAt(Carbon::now()->addMonth());
$subscription->save();
```
```php
// Set multiple subscriptions valid for one year
Subscription::whereKey([1, 2, 3])->expiresAt(Carbon::now()->addYear());
```

#### Using lifetime()
The `lifetime` method provides you a more human readable way to set the period of validity with a string.
The parameter must be a string and the syntax is the same as the syntax accepted by the Carbon `add` method
(see documentation here: [https://carbon.nesbot.com/docs/#api-addsub](https://carbon.nesbot.com/docs/#api-addsub)).

```php
// Create a new subscription valid for one month
$subscription = new Subscription();
$subscription->lifetime('1 month');
$subscription->save();
```

### Make existing models expire

If you want a model to expire, use the `expire` method on a model instance.
This will set the expiration date at the current timestamp.

```php
// Make a model expire
$subscription->expire();
```

#### Expire models by key

If you know the primary key of the model, you may make it expire without retrieving it by calling the `expireByKey` method.
In addition to a single primary key as its argument, the `expireByKey` method will accept multiple primary keys,
an array of primary keys, or a collection of primary keys: 

```php
Subscription::expireByKey(1);

Subscription::expireByKey(1, 2, 3);

Subscription::expireByKey([1, 2, 3]);

Subscription::expireByKey(collect([1, 2, 3]));
```

#### Expire models by query

You can also run an expire statement on a set of models:
```php
Subscription::where('plan', 'basic')->expire();
```

### Revive expired models

After a model has expired, you can make it valid again using `revive()` method.
It accepts an optional parameter which can be a date object or `null` for the new period of validity.
Without parameter it resets to the default expiration date or set the expiration attribute to `null` if no default
expiration date is set (making the model eternal).

```php
// Reset validity with the default expiration date or set validity for unlimited period
$subscription->revive();

// Set the model to expire in one month
$subscription->revive(Carbon::now()->addMonth());
```

Sure, it also works with queries:

```php
// Revive by query
Subscription::where('plan', 'plus')->revive();
```

### Make existing models eternal

If you want a model never to expire, you just have to set the expiration attribute to `null`.
You can do that manually or for existing models you can use the provided shortcut method `makeEternal()`:

```php
// Make a model eternal
$subscription->makeEternal();
```
```php
// Make eternal by query
Subscription::where('plan', 'business')->makeEternal();
```

### Extend model lifetime

With the `extendLifetimeBy`, you can extend the model lifetime by a human readable period (using the same syntax as the `lifetime` method): 

```php
$subscription->extendLifetimeBy('1 month')->save();
```

In the same way, you have the ability to shorten the model lifetime with the `shortenLifetimeBy` method:

### Shorten model lifetime

```php
$subscription->shortenLifetimeBy('3 days')->save();
```

### Reset the expiration date to default

You can reset the expiration date to its default value (`null` or the date returned by the `defaultExpiresAt` static function):

```php
$subscription->resetExpiration()->save();
```

### Get the status of a model

You can call the `isExpired()` and `isEternal()` methods on an expirable model instance. For example:

```php
if ($subscription->isExpired()) {
    $user->notify(new RenewalProposal($subscription));
}
```

### Purge expired records

This package comes with a command to delete expired records from the database.

In order to indicate that a model should be purged, add its class to the `purge` array of
the configuration file:

```php
    'purge' => [
        \App\Models\Subscription::class,
    ],
```

Then, run this command: `php artisan expirable:purge`

## License

Open source, licensed under the [MIT license](LICENSE).
