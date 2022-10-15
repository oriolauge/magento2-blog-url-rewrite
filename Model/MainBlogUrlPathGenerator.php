<?php
namespace OAG\BlogUrlRewrite\Model;
use OAG\BlogUrlRewrite\Model\Config;
use OAG\BlogUrlRewrite\Model\AbstractUrlPathGenerator;
use Magento\Store\Model\StoreManagerInterface;

class MainBlogUrlPathGenerator extends AbstractUrlPathGenerator
{
    /**
     * @var Config
     */
    protected $config;

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
        parent::__construct($config, $storeManager);

    }

    /**
     * Retrieve main blog url with suffix
     *
     * @param int $storeId
     * @return string
     */
    public function getMainBlogUrlPathWithSuffix($storeId = null): string
    {
        return $this->getBlogRoute($storeId)
            . $this->getUrlSuffix($storeId);
    }
}
