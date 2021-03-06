<?php

/*
 * This file is part of the puli/cli package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Cli;

use Puli\Cli\Command\DumpCommand;
use Puli\Cli\Command\LsCommand;
use Puli\Cli\Command\PackageCommand;
use Puli\Cli\Command\TagCommand;
use Webmozart\Console\Command\HelpCommand;
use Webmozart\Console\Application;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class PuliCliApplication extends Application
{
    const VERSION = '@package_version@';

    const RELEASE_DATE = '@release_date@';

    public function __construct()
    {
        parent::__construct('puli', self::VERSION);

        $this->setCatchExceptions(true);
        $this->setDefaultCommand('help');
    }

    protected function getDefaultCommands()
    {
        $rootDir = realpath(__DIR__.'/..');

        return array_merge(array(
            new HelpCommand(array(
                'manDir' => $rootDir.'/docs/man',
                'asciiDocDir' => $rootDir.'/docs',
                'commandPrefix' => 'puli-',
                'defaultPage' => 'puli',
            )),
            new DumpCommand(),
            new LsCommand(),
            new PackageCommand(),
            new TagCommand(),
        ));
    }
}
