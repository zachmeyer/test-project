<?php
namespace Application\Module;

use Atlas\Orm\AtlasContainer;
use Aura\Di\Container;
use Cadre\Module\Module;

class AtlasOrm extends Module
{
    public function define(Container $di)
    {
        $atlasContainerClass = AtlasContainer::class;

        $di->set('atlas:container', $di->lazyNew($atlasContainerClass));
        $di->set('atlas', $di->lazyGetCall('atlas:container', 'getAtlas'));

        $conn = include(__ROOTDIR__ . '/config/conn.php');

        $di->params[$atlasContainerClass] = [
            'dsn'        => $conn[0],
            'username'   => $conn[1],
            'password'   => $conn[2],
            'options'    => [],
            'attributes' => [],
        ];

        $pattern = __DIR__ . '/../AtlasOrm/DataSource/*/*Mapper.php';
        $mappers = glob($pattern);
        foreach ($mappers as $i => $file) {
            $mappers[$i] = 'Application\\AtlasOrm\\'
                         . str_replace('/', '\\', substr($file, strpos($file, 'DataSource/'), -4));
        }

        $di->setters[$atlasContainerClass]['setMappers'] = $mappers;

        $pattern = __DIR__ . '/../AtlasOrm/Gateway/*.php';
        $gateways = glob($pattern);
        foreach ($gateways as $i => $file) {
            $gateway = 'Application\\AtlasOrm\\'
                         . str_replace('/', '\\', substr($file, strpos($file, 'Gateway/'), -4));

            $di->params[$gateway]['atlas'] = $di->lazyGet('atlas');
        }
    }
}
