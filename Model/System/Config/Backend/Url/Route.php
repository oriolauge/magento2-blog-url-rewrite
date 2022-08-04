<?php

/**
 * Url rewrite suffix backend
 */
namespace OAG\BlogUrlRewrite\Model\System\Config\Backend\Url;
use OAG\BlogUrlRewrite\Model\System\Config\Backend\Url;
use OAG\BlogUrlRewrite\Model\Config;
use OAG\BlogUrlRewrite\Model\PostUrlRewriteGenerator;

/**
 * URL route backend model
 * 
 * This class is copied from 
 * Magento\Catalog\Model\System\Config\Backend\Catalog\Url\Rewrite\Suffix
 * 
 * The reason to copy this class is to adapt to route requeriment
 */
class Route extends Url
{
    /**
     * Check url rewrite suffix - whether we can support it
     *
     * @return $this
     */
    public function beforeSave()
    {
        $this->urlRewriteHelper->validateRequestPath($this->getValue());
        return $this;
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $this->updateRouteForUrlRewrites();
        }
        return parent::afterSave();
    }

    /**
     * {@inheritdoc}
     */
    public function afterDeleteCommit()
    {
        if ($this->isValueChanged()) {
            $this->updateRouteForUrlRewrites();
        }

        return parent::afterDeleteCommit();
    }

    /**
     * Update route for url rewrites
     *
     * @return $this
     */
    protected function updateRouteForUrlRewrites()
    {
        $map = [
            Config::XML_PATH_PERMALINK_ROUTE => PostUrlRewriteGenerator::ENTITY_TYPE,
        ];
        return $this->updateValueForUrlRewrites(
            $map,
            '~^' . preg_quote($this->getOldValue()) . '~'
        );
    }
}
