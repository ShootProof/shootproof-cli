<?php
/**
 * This file is part of the ShootProof command line tool.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) ShootProof, LLC (https://www.shootproof.com)
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ShootProof\Cli\Command;

use Aura\Cli\Context;
use ShootProof\Cli\Options;
use ShootProof\Cli\OptionsFactory;
use Sp_Api as ShootProofApi;

/**
 * Provides the shootproof-cli list-brands command
 */
class BrandsCommand extends BaseCommand implements HelpableCommandInterface
{
    use HelpableCommandTrait;

    /**
     * @var string
     */
    public static $usage = 'brands [options]';

    /**
     * @var string
     */
    public static $description = <<<TEXT
Lists the brands for a ShootProof account.
TEXT;

    /**
     * @var array
     */
    public static $options = [];

    /**
     * Called when this object is called as a function
     *
     * @param Context $context
     * @param OptionsFactory $optionsFactory
     * @throws \Exception if haltOnError setting is true
     */
    public function __invoke(Context $context, OptionsFactory $optionsFactory)
    {
        $getopt = $context->getopt(array_keys(self::$options));

        // Load base options
        $baseOptions = $optionsFactory->newInstance();
        $baseOptions->validateAllRequired();

        $result = $this->api->getBrands();
        $this->stdio->outln(json_encode($result['brands'], JSON_PRETTY_PRINT));
    }

    protected function processDirectory($dir, Options $baseOptions, OptionsFactory $optionsFactory)
    {
    }
}
