<?php

namespace ALajusticia\Expirable\PHPStan;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Signatures of the local builder macros registered by ExpirationScope.
 *
 * @internal
 * @template TModel of Model
 *
 * @method Builder<TModel> withExpired()
 * @method Builder<TModel> onlyExpired()
 * @method Builder<TModel> onlyEternal()
 * @method Builder<TModel> expiredSince(string $period)
 * @method int expire()
 * @method int revive(DateTimeInterface|string|null $newExpirationDate = null)
 * @method int makeEternal()
 * @method int expiresAt(DateTimeInterface|null $expirationDate)
 * @method int lifetime(string|null $period)
 */
interface ExpirableBuilderMethods
{
}
