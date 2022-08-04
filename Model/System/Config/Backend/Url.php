<?php

/**
 * Url rewrite suffix backend
 */
namespace OAG\BlogUrlRewrite\Model\System\Config\Backend;
use Magento\Framework\App\Config\Value;
use Magento\UrlRewrite\Helper\UrlRewrite as UrlRewriteHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Model\Storage\DbStorage;

/**
 * URL backend model
 * 
 * This class is copied from 
 * Magento\Catalog\Model\System\Config\Backend\Catalog\Url\Rewrite\Suffix
 * 
 * This class is created to group methods that we reuse in Route and Suffix classes.
 */
class Url extends Value
{
    /**
     * @var UrlRewriteHelper
     */
    protected $urlRewriteHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\App\Config
     */
    protected $appConfig;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        UrlRewriteHelper $urlRewriteHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ResourceConnection $appResource,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
        $this->urlRewriteHelper = $urlRewriteHelper;
        $this->storeManager = $storeManager;
        $this->connection = $appResource->getConnection();
        $this->urlFinder = $urlFinder;
        $this->resource = $appResource;
    }

    /**
     * Get instance of ScopePool
     *
     * @return \Magento\Framework\App\Config
     * @deprecated 102.0.0
     */
    protected function getAppConfig()
    {
        if ($this->appConfig === null) {
            $this->appConfig = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Config::class
            );
        }
        return $this->appConfig;
    }

    /**
     * Update value for url rewrites
     *
     * @param array $map
     * @param string $oldValuePattern
     * @return $this
     */
    protected function updateValueForUrlRewrites(array $map, string $oldValuePattern)
    {
        if (!isset($map[$this->getPath()])) {
            return $this;
        }
        $dataFilter = [UrlRewrite::ENTITY_TYPE => $map[$this->getPath()]];
        $storesIds = $this->getStoreIds();
        if ($storesIds) {
            $dataFilter[UrlRewrite::STORE_ID] = $storesIds;
        }
        $entities = $this->urlFinder->findAllByData($dataFilter);
        if ($this->getValue() !== null) {
            $value = $this->getValue();
        } else {
            $this->getAppConfig()->clean();
            $value = $this->_config->getValue($this->getPath());
        }
        foreach ($entities as $urlRewrite) {
            $bind = $urlRewrite->getIsAutogenerated()
                ? [UrlRewrite::REQUEST_PATH => preg_replace($oldValuePattern, $value, $urlRewrite->getRequestPath())]
                : [UrlRewrite::TARGET_PATH => preg_replace($oldValuePattern, $value, $urlRewrite->getTargetPath())];
            $this->connection->update(
                $this->resource->getTableName(DbStorage::TABLE_NAME),
                $bind,
                $this->connection->quoteIdentifier(UrlRewrite::URL_REWRITE_ID) . ' = ' . $urlRewrite->getUrlRewriteId()
            );
        }
        return $this;
    }

    /**
     * @return array|null
     */
    protected function getStoreIds()
    {
        if ($this->getScope() == 'stores') {
            $storeIds = [$this->getScopeId()];
        } elseif ($this->getScope() == 'websites') {
            $website = $this->storeManager->getWebsite($this->getScopeId());
            $storeIds = array_keys($website->getStoreIds());
            $storeIds = array_diff($storeIds, $this->getOverrideStoreIds($storeIds));
        } else {
            $storeIds = array_keys($this->storeManager->getStores());
            $storeIds = array_diff($storeIds, $this->getOverrideStoreIds($storeIds));
        }
        return $storeIds;
    }

    /**
     * @param array $storeIds
     * @return array
     */
    protected function getOverrideStoreIds($storeIds)
    {
        $excludeIds = [];
        foreach ($storeIds as $storeId) {
            $suffix = $this->_config->getValue($this->getPath(), ScopeInterface::SCOPE_STORE, $storeId);
            if ($suffix != $this->getOldValue()) {
                $excludeIds[] = $storeId;
            }
        }
        return $excludeIds;
    }
}
