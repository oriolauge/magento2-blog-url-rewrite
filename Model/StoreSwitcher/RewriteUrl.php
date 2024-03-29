<?php
/**
 * @see: Magento\UrlRewrite\Model\StoreSwitcher\RewriteUrl;
 */
declare(strict_types=1);

namespace OAG\BlogUrlRewrite\Model\StoreSwitcher;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcherInterface;
use Magento\Framework\HTTP\PhpEnvironment\RequestFactory;
use OAG\BlogUrlRewrite\Model\MainBlogUrlPathGenerator;
use Magento\Framework\UrlInterface;

/**
 * Handle url rewrites for redirect url
 */
class RewriteUrl implements StoreSwitcherInterface
{
    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var MainBlogUrlPathGenerator
     */
    protected $mainBlogUrlPathGenerator;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @param RequestFactory $requestFactory
     * @param MainBlogUrlPathGenerator $mainBlogUrlPathGenerator
     * @param UrlInterface $url
     */
    public function __construct(
        RequestFactory $requestFactory,
        MainBlogUrlPathGenerator $mainBlogUrlPathGenerator,
        UrlInterface $url
    ) {
        $this->requestFactory = $requestFactory;
        $this->mainBlogUrlPathGenerator = $mainBlogUrlPathGenerator;
        $this->url = $url;
    }

    /**
     * Switch to another store and maintain to main blog page.
     * 
     * Post page are using UrlRewrite module, so this logic is implemented by
     * Magento by default. Also, you can see OAG_BlogUrlRewrite module.
     * 
     * @param StoreInterface $fromStore
     * @param StoreInterface $targetStore
     * @param string $redirectUrl
     * @return string
     */
    public function switch(StoreInterface $fromStore, StoreInterface $targetStore, string $redirectUrl): string
    {
        $targetUrl = $redirectUrl;
        /** @var \Magento\Framework\HTTP\PhpEnvironment\Request $request */
        $request = $this->requestFactory->create(['uri' => $targetUrl]);
        $urlPath = ltrim($request->getPathInfo(), '/');

        if ($targetStore->isUseStoreInUrl()) {
            // Remove store code in redirect url for correct rewrite search
            $storeCode = preg_quote($targetStore->getCode() . '/', '/');
            $pattern = "@^($storeCode)@";
            $urlPath = preg_replace($pattern, '', $urlPath);
        }

        //Check if we come from main blog page
        $fromMainBlogUrl = $this->mainBlogUrlPathGenerator->getMainBlogUrlPathWithSuffix($fromStore->getStoreId());
        if (preg_match('#^' . $fromMainBlogUrl . '$#', $urlPath)) {
            /**
             * If we come from main blog page, we will redirect to correct configured
             * url in specific storeview
             */
            $toMainBlogUrl = $this->mainBlogUrlPathGenerator->getMainBlogUrlPathWithSuffix($targetStore->getStoreId());
            return $this->url->getDirectUrl($toMainBlogUrl);

        }
        
        return $targetUrl;
    }
}
