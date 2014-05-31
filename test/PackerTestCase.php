<?php

namespace Reload\Repack\Test;

use Composer\Config;
use Composer\IO\NullIO;
use Composer\Json\JsonFile;
use Composer\Package\Package;
use Composer\Package\PackageInterface;
use Composer\Repository\ComposerRepository;
use Composer\Repository\RepositoryInterface;
use Reload\Repack\Source\Source;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit_Framework_TestCase;

/**
 * Base class for Packer test cases.
 *
 * This provides shared plumbing.
 */
class PackerTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Base directory for test case output.
     *
     * @var vfsStreamDirectory
     */
    protected $dir;

    public function setUp()
    {
        parent::setUp();

        // Test cases use a sub directory named after their class name.
        $this->dir = vfsStream::setup(basename(__FILE__, '.php'));
    }

    /**
     * Get a path to a subdirectory for the test case.
     *
     * @param string $subDir
     * @return string The full path to the subdirectory.
     */
    protected function getDir($subDir = null)
    {
        return vfsStream::url(basename(__FILE__, '.php') . DIRECTORY_SEPARATOR . $subDir);
    }

    /**
     * Retrieve a set of test packages to use for testing purposes.
     *
     * @return Package[] Test packages.
     */
    protected function getPackages()
    {
        // The keys are important here. They are used as entries for the source and as a basis for
        // partitioning them as well.
        $packages = array(
            'foo/bar' => new Package('foo/bar', '1.2.3', "Foo/Bar-1.2.3"),
            'baz/boink' => new Package('baz/boink', '4.5.6', "Baz/Boink-4.5.6"),
            'bif/bof' => new Package('bif/bof', '7.8.9', "Bif/Bof-7.8.9"),
        );

        return $packages;
    }

    /**
     * Get a mock source for a set of packages.
     *
     * @param Package[] $packages The packages to include with the source.
     * @return \Reload\Repack\Source\Source A mock source implementation.
     */
    protected function getSource(array $packages)
    {
        $source = $this->getMock('Reload\Repack\Source\Source');
        // The package keys are returned as entries.
        $source->expects($this->any())
            ->method('getEntries')
            ->will($this->returnValue(array_keys($packages)));
        // The source will partition the packages by the first letter of the corresponding entry.
        $source->expects($this->any())
            ->method('getPartition')
            ->will(
                $this->returnCallback(
                    function ($source) {
                        return substr($source, 0, 1);
                    }
                )
            );
        // The source returns the package which matches the entry based on keys.
        $source->expects($this->any())
            ->method('getPackages')
            ->will(
                $this->returnCallback(
                    function ($entries) use ($packages) {
                        // We get the entries as array values. To use array_intersect_key to filter we need the entries
                        // as keys as well. array_flip to the rescue.
                        return array_intersect_key($packages, array_flip($entries));
                    }
                )
            );

        return $source;
    }

    /**
     * Returns a Composer repository for a packages.json file.
     *
     * @param JsonFile $file The packages.json file for the repository.
     * @return ComposerRepository The corresponding repository.
     */
    protected function getRepository(JsonFile $file)
    {
        $config = new Config();
        $config->merge(
            array(
                'config' => array(
                    'home' => $this->getDir('composer-home'),
                ),
            )
        );

        return new ComposerRepository(array('url' => $file->getPath()), new NullIO(), $config);
    }

    /**
     * Compares the contents of a repository with an array to test that they contain the same packages.
     *
     * @param RepositoryInterface $repository The repository
     * @param PackageInterface[] $expectedPackages The packages.
     */
    protected function compareRepositoryPackages(RepositoryInterface $repository, array $expectedPackages)
    {
        $repositoryPackages = $repository->getPackages();
        foreach ($repositoryPackages as $i => $package) {
            foreach ($expectedPackages as $j => $expectedPackage) {
                // Packages are considered equal if they have the same name and version.
                if ($expectedPackage->getName() == $package->getName() &&
                    $expectedPackage->getVersion() == $package->getVersion()
                ) {
                    // A match is found! Remove the packages from both sources.
                    unset($repositoryPackages[$i]);
                    unset($expectedPackages[$j]);
                    break;
                }
            }
        }
        // If all packages from both sources have been found there should be no leftovers.
        $this->assertEmpty($repositoryPackages);
        $this->assertEmpty($expectedPackages);
    }
}
