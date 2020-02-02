# Laravel Expirable

Inspired by the SoftDeletes trait, this package provides a trait to make Eloquent models expirable.

It relies on an additional attribute (named `expires_at` by default) that contains the date of expiration
(or `null` to make the model eternal).

When the expiration date is reached, the model will automatically disappear from all the Eloquent query results
(but still remain in the database).

* [Installation](#installation)
  * [Prepare your model](#prepare-your-model)
    * [Change the default name of the attribute](#change-the-default-name-of-the-attribute)
    * [Set a default period of validity](#set-a-default-period-of-validity)
  * [Prepare your migration](#prepare-your-migration)
* [Usage](#usage)
  * [Retrieving models](#retrieving-models)
    * [Retrieving valid models](#retrieving-valid-models)
    * [Retrieving all models](#retrieving-all-models)
    * [Retrieving only expired models](#retrieving-only-expired-models)
    * [Retrieving only eternal models](#retrieving-only-eternal-models)
    * [Retrieving expired models since](#retrieving-expired-models-since)
  * [Set the expiration date manually](#set-the-expiration-date-manually)
    * [The basic way](#the-basic-way)
    * [Using expiresAt()](#using-expiresat)
    * [Using lifetime()](#using-lifetime)
  * [Make existing models expire](#make-existing-models-expire)
    * [Expire models by key](#expire-models-by-key)
    * [Expire models by query](#expire-models-by-query)
  * [Revive expired models](#revive-expired-models)
  * [Make existing models eternal](#make-existing-models-eternal)
  * [Get the status of a model](#get-the-status-of-a-model)

## Installation

Install the package via composer using this command:

```bash
composer require alajusticia/laravel-expirable
```

The service provider will automatically get registered. Or you may manually add it in your `config/app.php` file:

```php
'providers' => [
    // ...
    ALajusticia\Expirable\ExpirableServiceProvider::class,
];
```

You can publish the configuration file with:

```bash
php artisan vendor:publish --provider="ALajusticia\Expirable\ExpirableServiceProvider" --tag="config"
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

#### Change the default name of the attribute

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
the expirable.php configuration file (the constant prevails).

#### Set a default period of validity

You can set a default period of validity with the `defaultExpiresAt` public static function. This function must return
a date object or `null`. This way, on saving the model the date of expiration will be automatically added unless you
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
 $usbscription = new App\Subscription;
 $subscription->save();
 
 // Create a new subscription which will expire in one year (overwrite the default expiration date)
  $usbscription = new App\Subscription;
  $subscription->expiresAt(Carbon::now()->addYear());
  $subscription->save();
 ```

### Prepare your migration

The package requires that you add the expirable column in your migration.
For convenience, the package provides a Blueprint macro ready to use in your migration file:

```php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            // ...

            $table->expirable();
        });
    }
```

By default the name of the database column, like the model attribute, is `expires_at`.
If you modified the default name of the attribute on your model, you need to set the same custom name for the column
in your migration, by giving the macro a parameter with the name.

To continue with our subscription example:

```php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            // ...

            $table->expirable('ends_at');
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
$subscriptions = App\Subscription::all();
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
$subscriptions = App\Subscription::withExpired->get();
```

#### Retrieving only expired models

Use the onlyExpired scope:

```php
// Get expired subscriptions
$subscriptions = App\Subscription::onlyExpired->get();
```

#### Retrieving only eternal models

To get only the models which do not expire (with expiration date attribute to `null`), use the onlyEternal scope:

```php
// Get unlimited subscriptions
$subscriptions = App\Subscription::onlyEternal->get();
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
App\Subscription::expiredSince('1 year')->delete();
```

### Set the expiration date manually

#### The basic way

If you know the name of the expiration date attribute, you can simply populate this attribute
with a date object (or `null` for eternal):

```php
// Create a new subscription valid for one month
$subscription = new App\Subscription();
$subscription->ends_at = Carbon::now()->addMonth();
$subscription->save();
```

Of course it also works with mass assignment, but don't forget to add the attribute you intend to mass assign
(here `ends_at`) in the `$fillable` property of your model:

```php
// Create a new subscription valid for one month
$subscription = App\Subscription::create([
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
$subscription = new App\Subscription();
$subscription->expiresAt(Carbon::now()->addMonth());
$subscription->save();
```
```php
// Set multiple subscriptions valid for one year
Subscription::find([1, 2, 3])->expiresAt(Carbon::now()->addYear());
```

#### Using lifetime()
The `lifetime` method provides you a more human readable way to set the period of validity with a string.
The parameter must be a string and the syntax is the same as the syntax accepted by the Carbon `add` method
(see documentation here: [https://carbon.nesbot.com/docs/#api-addsub](https://carbon.nesbot.com/docs/#api-addsub)).

```php
// Create a new subscription valid for one month
$subscription = new App\Subscription();
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
App\Subscription::expireByKey(1);

App\Subscription::expireByKey(1, 2, 3);

App\Subscription::expireByKey([1, 2, 3]);

App\Subscription::expireByKey(collect([1, 2, 3]));
```

#### Expire models by query

You can also run an expire statement on a set of models:
```php
App\Subscription::where('plan', 'basic')->expire();
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
App\Subscription::where('plan', 'plus')->revive();
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
App\Subscription::where('plan', 'business')->makeEternal();
```

### Get the status of a model

You can call the `isExpired()` and `isEternal()` methods on an expirable model instance. For example:

```php
if ($subscription->isExpired()) {
    $user->notify(new RenewalProposal($subscription));
}
```