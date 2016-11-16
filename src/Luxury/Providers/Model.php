<?php

namespace Luxury\Providers;

use Luxury\Constants\Services;

use Luxury\Interfaces\Providable;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Manager as ModelManager;
use Phalcon\Mvc\Model\Metadata\Memory as ModelMetadataMemory;
use Phalcon\Mvc\Model\Transaction\Manager as ModelTransactionManager;

/**
 * Class Model
 *
 * @package     Luxury\Providers
 */
class Model extends Injectable  implements Providable
{
    /**
     * @inheritdoc
     */
    public function registering()
    {
        $di = $this->getDI();

        $di->setShared(Services::MODELS_MANAGER, ModelManager::class);
        $di->setShared(Services::MODELS_METADATA, ModelMetadataMemory::class);
        $di->setShared(Services::TRANSACTION_MANAGER, ModelTransactionManager::class);
    }
}
