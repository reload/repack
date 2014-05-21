<?php

namespace Reload\Repack\Test;

use Reload\Repack\Packer\SimplePacker;

/**
 * Test case for testing the ProviderPackager.
 */
class SimplePackerTest extends PackerTestCase
{

    /**
     * Simple test case.
     *
     * Does a source of packages translate to a valid Composer repository containing the same packages?
     */
    public function testSimplePacker()
    {
        // Setup the prerequisites for the packer.
        $packages = $this->getPackages();
        $source = $this->getSource($packages);
        $dir = $this->getDir();

        // Run the packer to generate the files.
        $packager = new SimplePacker($source, $dir);
        $files = $packager->generate();

        // A SimplePacker should only return a single file which is the packages.json file.
        // Use this as a basis for a Composer repository.
        $file = reset($files);
        $repository = $this->getRepository($file);

        // Compare the packages in the repository with the ones from the source.
        $this->compareRepositoryPackages($repository, $packages);
    }


} 
