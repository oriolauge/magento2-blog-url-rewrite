<?php
namespace OAG\BlogUrlRewrite\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use OAG\BlogUrlRewrite\Model\PostUrlRewriteGenerator;

class Uninstall implements UninstallInterface
{
    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @inheritDoc
     *
     * @param UrlPersistInterface $urlPersist
     */
    public function __construct(
        UrlPersistInterface $urlPersist
    )
    {
        $this->urlPersist = $urlPersist;
    }

    /**
     * Uninstall all data module. In this case, we will remove all urls from url rewrite table
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $this->urlPersist->deleteByData(
            [UrlRewrite::ENTITY_TYPE => PostUrlRewriteGenerator::ENTITY_TYPE]
        );
    }
}
