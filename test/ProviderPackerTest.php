<?php

namespace Reload\Repack\Test;

use Composer\Json\JsonFile;
use Reload\Repack\Packer\ProviderPacker;

/**
 * Test case for testing the ProviderPackager.
 */
class ProviderPackerTest extends PackerTestCase
{

    /**
     * Simple test case.
     *
     * Does a source of packages translate to a valid Composer repository containing the same packages?
     */
    public function testProviderPacker()
    {
        // Setup the prerequisites for the packer.
        $packages = $this->getPackages();
        $source = $this->getSource($packages);
        $dir = $this->getDir();

        // Run the packer to generate the files.
        $packager = new ProviderPacker($source, $dir);
        $files = $packager->generate();

        // Locate the packages.json file and use it as base for a Composer repository
        $files = array_filter($files, function (JsonFile $file) {
                return basename($file->getPath()) == 'packages.json';
        });
        $repository = $this->getRepository(reset($files));

        // Compare provider names with names from the packages. It is not possible to
        // get all packages from a provider-based Composer repository so we have to
        // stick with the names for now.
        $this->assertEquals(array_keys($packages), $repository->getProviderNames());
    }
}
