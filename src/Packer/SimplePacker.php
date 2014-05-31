<?php

namespace Reload\Repack\Packer;

use Composer\Json\JsonFile;
use Reload\Repack\Packer\Packer;

/**
 * A Packer implementation which generates a single packages.json file containing all packages.
 *
 * This is suitable for smaller repositories.
 *
 * @see https://getcomposer.org/doc/05-repositories.md#packages
 */
class SimplePacker extends Packer
{

    /**
     * @inheritDoc
     */
    public function generate()
    {
        // Get all packages. There is no need to partition them here.
        $packages = array();
        foreach ($this->source->getPackages($this->source->getEntries()) as $package) {
            if (!isset($packages[$package->getName()])) {
                $packages[$package->getName()] = array();
            }
            $packages[$package->getName()][$package->getVersion()] = $this->dumper->dump($package);
        }

        // Write all packages to a packages.json file.
        $file = new JsonFile($this->dir . 'packages.json');
        $file->write(array('packages' => $packages));

        return array($file);
    }
}
