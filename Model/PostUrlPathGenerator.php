<?php
namespace OAG\BlogUrlRewrite\Model;
use OAG\Blog\Api\Data\PostInterface;
use OAG\BlogUrlRewrite\Model\AbstractUrlPathGenerator;
use OAG\BlogUrlRewrite\Model\Config;
use Magento\Framework\Filter\FilterManager;
use Magento\Store\Model\StoreManagerInterface;

class PostUrlPathGenerator extends AbstractUrlPathGenerator
{
    /**
     * @var FilterManager
     */
    protected $filterManager;

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
        FilterManager $filterManager,
        Config $config,
        StoreManagerInterface $storeManager
    ) {
        $this->filterManager = $filterManager;
        $this->config = $config;
        $this->storeManager = $storeManager;
        parent::__construct($config, $storeManager);
    }

    /**
     * Get canonical post url path
     *
     * @param PostInterface $post
     * @return string
     */
    public function getCanonicalUrlPath(PostInterface $post)
    {
        return 'oagblog/post/view/id/' . $post->getId();
    }

    /**
     * Generate CMS page url key based on url_key entered by merchant or page title
     *
     * @param PostInterface $post
     * @return string
     */
    public function generateUrlKey(PostInterface $post)
    {
        $urlKey = $post->getUrlKey();
        return $this->filterManager->translitUrl(empty($urlKey) ? $post->getTitle() : $urlKey);
    }

    /**
     * Generate post url with suffix and blog route
     *
     * @param PostInterface $post
     * @param mixed $storeId
     * @return string
     */
    public function getUrlPathWithSuffixAndBlogRoute(PostInterface $post, $storeId = null): string
    {
        return $this->getBlogRoute($storeId)
            . '/'
            . $post->getUrlKey()
            . $this->getUrlSuffix($storeId);
    }
}
