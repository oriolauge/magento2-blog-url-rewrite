<?php
namespace OAG\BlogUrlRewrite\Plugin\Model\ResourceModel;
use OAG\Blog\Model\ResourceModel\Post as PostResourceModel;
use OAG\BlogUrlRewrite\Model\PostUrlPathGenerator;
use OAG\Blog\Api\Data\PostInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use OAG\BlogUrlRewrite\Model\PostUrlRewriteGenerator;
use Magento\UrlRewrite\Model\UrlPersistInterface;

/**
 * Before save and after delete plugin for \OAG\Blog\Model\ResourceModel\Post:
 * - autogenerates url_key if the merchant didn't fill this field
 * - remove all url rewrites for post blog entitys on delete
 */
class Post
{
    /**
     * @var PostUrlPathGenerator
     */
    protected $postUrlPathGenerator;

    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @param PostUrlPathGenerator $postUrlPathGenerator
     */
    public function __construct(
        PostUrlPathGenerator $postUrlPathGenerator,
        UrlPersistInterface $urlPersist
    ) {
        $this->postUrlPathGenerator = $postUrlPathGenerator;
        $this->urlPersist = $urlPersist;
    }

    /**
     * Before save handler
     *
     * @param PostResourceModel $subject
     * @param AbstractModel $object
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        PostResourceModel $subject,
        AbstractModel $object
    ) {
        $useDefaultValue = !empty($object->getData('use_default')[PostInterface::KEY_URL_KEY]);
        if (!$useDefaultValue) {
            $object->setData(
                PostInterface::KEY_URL_KEY
                , $this->postUrlPathGenerator->generateUrlKey($object)
            );
        } else {
            $object->setData(PostInterface::KEY_URL_KEY, null);
        }
    }

    /**
     * On delete handler to remove related url rewrites
     *
     * @param PostResourceModel $subject
     * @param PostResourceModel $result
     * @param AbstractModel $object
     * @return PostResourceModel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        PostResourceModel $subject,
        PostResourceModel $result,
        AbstractModel $object
    ) {
        if ($object->isDeleted()) {
            $this->urlPersist->deleteByData(
                [
                    UrlRewrite::ENTITY_ID => $object->getId(),
                    UrlRewrite::ENTITY_TYPE => PostUrlRewriteGenerator::ENTITY_TYPE,
                ]
            );
        }

        return $result;
    }
}
