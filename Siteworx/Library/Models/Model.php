<?php

declare(strict_types=1);

namespace Siteworx\Library\Models;

use Carbon\Carbon;
use Illuminate\Container\Container;
use Illuminate\Database\Concerns\BuildsQueries;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\Eloquent\{Builder, Collection, Concerns\QueriesRelationships, Model as ORM};
use Siteworx\Library\Application\Core;

/**
 * Class Model
 *
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @method static Builder           where(\Closure|\string $column, string $operator = null, mixed $value = null, string $boolean = 'and') // @codingStandardsIgnoreLine
 * @method Model|Builder|null       first(array $columns = [])
 * @method static $this|null        find(int|string $id)
 * @method static $this             join(string $table, string $one, string $operator = null, string $two = null, string $type = 'inner', bool $where = false) // @codingStandardsIgnoreLine
 * @method Collection|Builder[]     get(array $columns = array('*'))
 * @method static Builder           orderBy(\string $column, string $direction = 'asc')
 * @method Builder                  forPage(int $page, int $perPage = 15)
 * @method static Builder           whereNotIn(string $column, array $values)
 * @method static Builder           whereNotNull(string $column)
 * @method static Builder           whereNull(string $column)
 * @method Builder                  newQuery()
 * @method static Builder           select(array $columns)
 * @method static Builder           whereIn(string $column, array $haystack, string $condition = 'and', bool $strict = false) // @codingStandardsIgnoreLine
 *
 * @package Siteworx\Models
 */
abstract class Model extends ORM
{
    use BuildsQueries;
    use QueriesRelationships;

    /**
     * Model constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        if (!ORM::getConnectionResolver() instanceof ConnectionResolver) {
            $dbContainer = new Container();
            $connFactory = new ConnectionFactory($dbContainer);
            $conn = $connFactory->make(
                Core::di()->config->get('settings.db')
            );

            $resolver = new ConnectionResolver();
            $resolver->addConnection('default', $conn);
            $resolver->setDefaultConnection('default');

            ORM::setConnectionResolver($resolver);
        }

        parent::__construct($attributes);
    }

    public function toArray($filter = []): array
    {
        if (\count($filter) === 0) {
            return parent::toArray();
        }

        $attributes = parent::toArray();

        $return = [];

        foreach ($attributes as $key => $value) {
            if (\in_array($key, $filter, true)) {
                $return[$key] = $value;
            }
        }

        return $return;
    }
}
