<?php

return PhpCsFixer\Config::create()
    ->setRules(array(
        '@PSR2' => true,
        '@Symfony' => true,
        '@PhpCsFixer' => true,
    ))
    ->setLineEnding("\n");
