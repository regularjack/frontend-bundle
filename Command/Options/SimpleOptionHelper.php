<?php

namespace Rj\FrontendBundle\Command\Options;

use Symfony\Component\Console\Question\Question;

class SimpleOptionHelper extends BaseOptionHelper
{
    /**
     * {@inheritdoc}
     */
    public function getQuestion($question)
    {
        return new Question("<question>$question</question>", $this->defaultValue);
    }
}
