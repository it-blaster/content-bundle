<?php

namespace Etfostra\ContentBundle\Model;

use Etfostra\ContentBundle\Model\om\BasePage;

class Page extends BasePage
{
    /**
     * @return string
     */
    public function __toString() {
        if ($this->isNew()) {
            return 'New Page';
        } else {
            return $this->getTitle();
        }
    }
}
