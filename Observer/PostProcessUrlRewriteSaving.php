<?php
namespace OAG\BlogUrlRewrite\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Framework\Event\ObserverInterface;
use OAG\BlogUrlRewrite\Model\PostUrlRewriteGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use OAG\Blog\Api\Data\PostInterface;

class PostProcessUrlRewriteSaving implements ObserverInterface
{
    /**
     * @var PostUrlRewriteGenerator
     */
    protected $postUrlRewriteGenerator;

    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @param PostUrlRewriteGenerator $postUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     */
    public function __construct(PostUrlRewriteGenerator $postUrlRewriteGenerator, UrlPersistInterface $urlPersist)
    {
        $this->postUrlRewriteGenerator = $postUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
    }

    /**
     * Generate urls for UrlRewrite and save it in storage
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var $cmsPage \Magento\Cms\Model\Page */
        $post = $observer->getEvent()->getEntity();

        if ($post->dataHasChangedFor(PostInterface::KEY_URL_KEY) || $post->dataHasChangedFor(PostInterface::KEY_STORE_ID)) {
            $urls = $this->postUrlRewriteGenerator->generate($post);

            $this->urlPersist->deleteByData([
                UrlRewrite::ENTITY_ID => $post->getId(),
                UrlRewrite::ENTITY_TYPE => PostUrlRewriteGenerator::ENTITY_TYPE,
            ]);
            $this->urlPersist->replace($urls);
        }
    }
}
