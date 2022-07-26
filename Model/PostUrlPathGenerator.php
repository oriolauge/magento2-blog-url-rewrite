<?php
namespace OAG\BlogUrlRewrite\Model;
use OAG\Blog\Api\Data\PostInterface;
use Magento\Framework\Filter\FilterManager;
use OAG\Blog\Model\Config;
use Magento\Store\Model\ScopeInterface;

class PostUrlPathGenerator
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
     * Cache for post rewrite suffix
     *
     * @var array
     */
    protected $postUrlSuffix = [];

    /**
     * Cache for blog route
     *
     * @var array
     */
    protected $blogRoute = [];

    /**
     * Construct function
     *
     * @param FilterManager $filterManager
     */
    public function __construct(
        FilterManager $filterManager,
        Config $config
    ) {
        $this->filterManager = $filterManager;
        $this->config = $config;
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
     * @param [type] $storeId
     * @return void
     */
    public function getUrlPathWithSuffixAndBlogRoute(PostInterface $post, $storeId = null)
    {
        return $this->getBlogRoute($storeId)
            . '/'
            . $post->getUrlKey()
            . $this->getPostUrlSuffix($storeId);
    }

    /**
     * Retrieve post rewrite suffix for store
     *
     * @param int $storeId
     * @return string
     */
    protected function getPostUrlSuffix($storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        if (!isset($this->postUrlSuffix[$storeId])) {
            $this->postUrlSuffix[$storeId] = $this->config->getPostSufix($storeId);
        }
        return $this->postUrlSuffix[$storeId];
    }

    /**
     * Retrieve post rewrite suffix for store
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
