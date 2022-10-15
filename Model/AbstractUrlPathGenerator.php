<?php
namespace OAG\BlogUrlRewrite\Model;
use OAG\BlogUrlRewrite\Model\Config;
use Magento\Store\Model\StoreManagerInterface;

abstract class AbstractUrlPathGenerator
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * Cache for post rewrite suffix
     *
     * @var array
     */
    protected $urlSuffix = [];

    /**
     * Cache for blog route
     *
     * @var array
     */
    protected $blogRoute = [];

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Construct function
     *
     * @param FilterManager $filterManager
     */
    public function __construct(
        Config $config,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve url suffix for store
     *
     * @param int $storeId
     * @return string
     */
    protected function getUrlSuffix($storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        if (!isset($this->urlSuffix[$storeId])) {
            $this->urlSuffix[$storeId] = $this->config->getUrlSufix($storeId);
        }
        return $this->urlSuffix[$storeId];
    }

    /**
     * Retrieve blog configured route
     *
     * @param int $storeId
     * @return string
     */
    protected function getBlogRoute($storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        if (!isset($this->blogRoute[$storeId])) {
            $this->blogRoute[$storeId] = $this->config->getBlogRoute($storeId);
        }
        return $this->blogRoute[$storeId];
    }
}
