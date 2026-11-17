<?php

declare(strict_types=1);

namespace CupNoodles\PriceByWeight\Models;

use Igniter\Cart\Models\Menu;
use Igniter\Flame\Database\Model;

/**
 * A unit items can be sold by, eg. Pounds (lb, 2 decimal places).
 *
 * @property int $uom_id
 * @property string $name
 * @property string $abbreviation
 * @property int $decimal_places Precision customers can order in, eg. 2 = 0.01 lb
 */
class Unit extends Model
{
    protected $table = 'units_of_measure';

    protected $primaryKey = 'uom_id';

    public $timestamps = false;

    protected $fillable = ['name', 'abbreviation', 'decimal_places'];

    protected $casts = [
        'decimal_places' => 'integer',
    ];

    public $relation = [
        'hasMany' => [
            'menus' => [Menu::class, 'foreignKey' => 'uom_id'],
        ],
    ];

    public static function getDropdownOptions(): array
    {
        return static::query()->orderBy('name')->get()
            ->mapWithKeys(fn(self $unit): array => [$unit->getKey() => $unit->name.' ('.$unit->abbreviation.')'])
            ->all();
    }

    /** Smallest amount that can be ordered, eg. 0.01 */
    public function precision(): float
    {
        return 10 ** -$this->decimal_places;
    }

    /** "1.25 lb", without trailing zeros */
    public function format(float|int|string $weight): string
    {
        $formatted = rtrim(rtrim(number_format((float)$weight, $this->decimal_places, '.', ','), '0'), '.');

        return $formatted.' '.$this->abbreviation;
    }
}
