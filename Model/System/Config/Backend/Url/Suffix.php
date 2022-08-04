<?php

/**
 * Url rewrite suffix backend
 */
namespace OAG\BlogUrlRewrite\Model\System\Config\Backend\Url;
use OAG\BlogUrlRewrite\Model\System\Config\Backend\Url;
use OAG\BlogUrlRewrite\Model\Config;
use OAG\BlogUrlRewrite\Model\PostUrlRewriteGenerator;

/**
 * URL suffix backend model
 * 
 * This class is copied from 
 * Magento\Catalog\Model\System\Config\Backend\Catalog\Url\Rewrite\Suffix
 * 
 * The reason to copy this class is to change the updateSuffixForUrlRewrites() function
 * to accept our classes. Also, we change afterDeleteCommit, beforeSave and AfterSave
 */
class Suffix extends Url
{
    /**
     * Check url rewrite suffix - whether we can support it
     *
     * @return $this
     */
    public function beforeSave()
    {
        $this->urlRewriteHelper->validateSuffix($this->getValue());
        return $this;
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $this->updateSuffixForUrlRewrites();
        }
        return parent::afterSave();
    }

    /**
     * {@inheritdoc}
     */
    public function afterDeleteCommit()
    {
        if ($this->isValueChanged()) {
            $this->updateSuffixForUrlRewrites();
        }

        return parent::afterDeleteCommit();
    }

    /**
     * Update suffix for url rewrites
     *
     * @return $this
     */
    protected function updateSuffixForUrlRewrites()
    {
        $map = [
            Config::XML_PATH_PERMALINK_POST_SUFIX => PostUrlRewriteGenerator::ENTITY_TYPE,
        ];
        return $this->updateValueForUrlRewrites(
            $map,
            '~' . preg_quote($this->getOldValue()) . '$~'
        );
    }
}
