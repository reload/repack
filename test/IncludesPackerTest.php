<?php

namespace Reload\Repack\Test;

use Composer\Json\JsonFile;
use Reload\Repack\Packer\IncludesPacker;

/**
 * Test case for testing the IncludesPacker.
 */
class IncludesPackerTest extends PackerTestCase
{

    /**
     * Simple test case.
     *
     * Does a source of packages translate to a valid Composer repository containing the same packages?
     */
    public function testIncludesPacker()
    {
        // Setup the prerequisites for the packer.
        $packages = $this->getPackages();
        $source = $this->getSource($packages);
        $dir = $this->getDir();

        // Run the packer to generate the files.
        $packager = new IncludesPacker($source, $dir);
        $files = $packager->generate();

        // Locate the packages.json file and use it as base for a Composer repository
        $files = array_filter($files, function (JsonFile $file) {
                return basename($file->getPath()) == 'packages.json';
        });
        $repository = $this->getRepository(reset($files));

        // Compare the packages in the repository with the ones from the source.
        $this->compareRepositoryPackages($repository, $packages);
    }

}
