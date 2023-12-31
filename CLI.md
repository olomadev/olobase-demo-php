
### Cli Commands

```sh
cd /var/www/project/bin/
php redis-listener.php local
php flush-cache.php
```

### Example Insert

```php
declare(strict_types=1);

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\ResultSet\ResultSet;

require '../vendor/autoload.php';
$container = require '../config/container.php';
$adapter = $container->get(AdapterInterface::class);
$rolePerm = new TableGateway('rolePermissions', $adapter, null, new ResultSet(ResultSet::TYPE_ARRAY));

$permissions = [
    '03c8e870-1b3e-4eec-a182-60e4e3027750',
    '05984351-556c-468c-b5c3-55988077afcc',
];
foreach ($permissions as $val) {
    $rolePerm->insert(['roleId' => '6be6178d-fe99-47b6-90d5-2a0c4d25b6dc', 'permId' => trim($val)]);
}
echo "ok".PHP_EOL;
```