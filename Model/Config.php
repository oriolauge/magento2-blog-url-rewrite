<?php

namespace OAG\BlogUrlRewrite\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * OAG Blog Config Model
 */
class Config
{
    /**
     * Hold general blog route config path
     */
    const XML_PATH_PERMALINK_ROUTE = 'oag_blog/permalink/route';

    /**
     * Hold url sufix config path
     */
    const XML_PATH_PERMALINK_URL_SUFIX = 'oag_blog/permalink/url_sufix';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get general blog route config value
     *
     * @param mixed $storeId
     * @return string
     */
    public function getBlogRoute($storeId = null)
    {
        return $this->getConfig(
            self::XML_PATH_PERMALINK_ROUTE,
            $storeId
        );
    }

    /**
     * Get url sufix config value
     *
     * @param mixed $storeId
     * @return string
     */
    public function getUrlSufix($storeId = null): string
    {
        return $this->getConfig(
            self::XML_PATH_PERMALINK_URL_SUFIX,
            $storeId
        );
    }

    /**
     * Retrieve store config value
     * @param string $path
     * @param mixed $storeId
     * @return mixed
     */
    protected function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
