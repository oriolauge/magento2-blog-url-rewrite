<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace OAG\BlogUrlRewrite\Model;
use Magento\Store\Model\StoreManagerInterface;
use OAG\BlogUrlRewrite\Model\PostUrlPathGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Magento\Store\Model\Store;
use OAG\Blog\Api\Data\PostInterface;
use OAG\Blog\Model\PostRepository;
use OAG\BlogUrlRewrite\Model\PostCheckOverriddenAttributePerStore;
use OAG\Blog\Setup\PostSetup;

class PostUrlRewriteGenerator
{
    /**
     * Entity type code
     */
    const ENTITY_TYPE = 'blog-post';

    /**
     * @var UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * @var PostUrlPathGenerator
     */
    protected $postUrlPathGenerator;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PostRepository
     */
    protected $postRepository;

    /**
     * @var PostCheckOverriddenAttributePerStore
     */
    protected $postCheckOverriddenAttributePerStore;

    /**
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param PostUrlPathGenerator $postUrlPathGenerator
     * @param StoreManagerInterface $storeManager
     * @param PostRepository $postRepository
     * @param PostCheckOverriddenAttributePerStore $postCheckOverriddenAttributePerStore
     */
    public function __construct(
        UrlRewriteFactory $urlRewriteFactory,
        PostUrlPathGenerator $postUrlPathGenerator,
        StoreManagerInterface $storeManager,
        PostRepository $postRepository,
        PostCheckOverriddenAttributePerStore $postCheckOverriddenAttributePerStore
    ) {
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->storeManager = $storeManager;
        $this->postUrlPathGenerator = $postUrlPathGenerator;
        $this->postRepository = $postRepository;
        $this->postCheckOverriddenAttributePerStore = $postCheckOverriddenAttributePerStore;
    }

    /**
     * @param PostInterface $post
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    public function generate(PostInterface $post)
    {
        $storeId = $post->getStoreId();
        $urls = $this->isGlobalScope($storeId) ? $this->generateForAllStores($post)
            : $this->generateForSpecificStoreView($post, $storeId);
        return $urls;
    }

    /**
     * Generate list of urls for default store
     *
     * @param PostInterface $post
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    protected function generateForAllStores(PostInterface $post)
    {
        $urls = [];
        foreach ($this->storeManager->getStores() as $store) {
            if (!$this->isGlobalScope($store->getStoreId())
                && $this->isOverrideUrlsForStore($store->getStoreId(), $post->getId())) {
                $postStore = $this->postRepository->getById($post->getId(), $store->getStoreId());
                $urls[] = $this->createUrlRewrite($postStore, $store->getStoreId());
            } else {
                $urls[] = $this->createUrlRewrite($post, $store->getStoreId());
            }
        }
        return $urls;
    }


    /**
     * Check if we have an specific value for a store id
     *
     * @param int $storeId
     * @param int $postId
     * @return boolean
     */
    protected function isOverrideUrlsForStore($storeId, $postId): bool
    {
        if (!$postId) {
            return false;
        }

        return $this->postCheckOverriddenAttributePerStore
            ->doesEntityHaveOverriddenUrlKeyForStore(
                $storeId,
                $postId,
                PostSetup::ENTITY_TYPE_CODE
            );

    }

    /**
     * Generate list of urls per store
     *
     * @param PostInterface $post
     * @param int $storeId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    protected function generateForSpecificStoreView(PostInterface $post, $storeId)
    {
        $urls[] = $this->createUrlRewrite($post, $storeId);
        return $urls;
    }

    /**
     * Create url rewrite object
     *
     * @param int $storeId
     * @param int $redirectType
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite
     */
    protected function createUrlRewrite(PostInterface $post, $storeId, $redirectType = 0)
    {
        return $this->urlRewriteFactory->create()->setStoreId($storeId)
            ->setEntityType(self::ENTITY_TYPE)
            ->setEntityId($post->getId())
            ->setRequestPath(
                $this->postUrlPathGenerator->getUrlPathWithSuffixAndBlogRoute(
                    $post,
                    $storeId
                )
            )->setTargetPath($this->postUrlPathGenerator->getCanonicalUrlPath($post))
            ->setIsAutogenerated(1)
            ->setRedirectType($redirectType);
    }

    /**
     * Check is global scope
     *
     * @param int|null $storeId
     * @return bool
     */
    protected function isGlobalScope($storeId): bool
    {
        return null === $storeId || $storeId == Store::DEFAULT_STORE_ID;
    }
}
