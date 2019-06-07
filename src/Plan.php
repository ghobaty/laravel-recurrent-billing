<?php namespace Ghobaty\Billing;

use InvalidArgumentException;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @property string  $id
 * @property string  $name
 * @property string  $description
 * @property bool    $active
 * @property integer $price
 * @property array   $quota
 */
class Plan implements Arrayable, Jsonable
{

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @param string $id
     * @return \Ghobaty\Billing\Plan
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public static function byId(string $id): \Ghobaty\Billing\Plan
    {
        $ret = self::all()->firstWhere('id', $id);

        if (! $ret) {
            throw new InvalidArgumentException("Unknown plan ID: {$id}");
        }

        return $ret;
    }

    /**
     * @param string $name
     * @return \Ghobaty\Billing\Plan
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public static function byName(string $name): \Ghobaty\Billing\Plan
    {
        $ret = self::all()->firstWhere('name', $name);

        if (! $ret) {
            throw new InvalidArgumentException("Unknown plan name: {$name}");
        }

        return $ret;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public static function free(): \Illuminate\Support\Collection
    {
        return self::all()->reject->price;
    }

    /**
     * @return \Illuminate\Support\Collection
     * @throws \LogicException
     */
    public static function paid(): \Illuminate\Support\Collection
    {
        return self::all()->filter->price;
    }

    /**
     * @return \Illuminate\Support\Collection
     * @throws \LogicException
     */
    public static function active(): \Illuminate\Support\Collection
    {
        return self::all()->filter->active;
    }

    /**
     * @return \Illuminate\Support\Collection
     * @throws \LogicException
     */
    public static function activeAndPaid(): \Illuminate\Support\Collection
    {
        return self::active()->filter->price;
    }

    /**
     * @return \Illuminate\Support\Collection
     * @throws \LogicException
     */
    public static function all(): \Illuminate\Support\Collection
    {
        return collect(config('billing.plans', []))->map(function (array $plan) {
            return new static($plan);
        });
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->active;
    }

    /**
     * @return bool
     */
    public function isInactive()
    {
        return ! $this->isActive();
    }

    /**
     * @return bool
     */
    public function isPaid()
    {
        return (bool)$this->price;
    }

    /**
     * @return bool
     */
    public function isFree()
    {
        return ! $this->isPaid();
    }

    /**
     * @param \Ghobaty\Billing\Plan $another
     * @return bool
     */
    public function is(Plan $another)
    {
        return $this->name === $another->name;
    }

    /**
     * @param \Ghobaty\Billing\Plan $another
     * @return bool
     */
    public function moreExpensiveThan(Plan $another)
    {
        return $this->price > $another->price;
    }


    /**
     * @param \Ghobaty\Billing\Plan $another
     * @return bool
     */
    public function cheaperThan(Plan $another)
    {
        return $this->price < $another->price;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * @param int $options
     * @return false|string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * @return false|string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Unset an attribute on the model.
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->attributes);
    }

    /**
     * Get the value for a given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->attributes[$offset] ?? null;
    }

    /**
     * Set the value for a given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->attributes[$offset] = $value;
    }

    /**
     * Unset the value for a given offset.
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }
}
