php
<?php

use Robo\Result;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see https://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    // define public methods as commands

    /**
     * Run PHPStan static analysis.
     */
    public function phpstan(): Result
    {
        return $this->taskExec('vendor/bin/phpstan analyse src --memory-limit=1G')->run();
    }

    /**
     * Run PHP CodeSniffer.
     */
    public function phpcs(): Result
    {
        return $this->taskExec('vendor/bin/phpcs src')->run();
    }

    /**
     * Run PHPUnit tests.
     */
    public function phpunit(): Result
    {
        return $this->taskPHPUnit()
            ->configFile('tests/phpunit.xml.dist')
            ->run();
    }

    /**
     * Run PHP CS Fixer.
     */
    public function phpcsf(): Result
    {
        return $this->taskExec('vendor/bin/php-cs-fixer fix --diff')->run();
    }

    /**
     * Run all quality checks.
     */
    public function quality(): void
    {
        $this->phpstan();
        $this->phpcs();
        $this->phpcsf();
        $this->phpunit();
    }
}