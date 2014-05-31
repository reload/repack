<?php

namespace Reload\Repack\Packer;

use Composer\Json\JsonFile;

/**
 * A Packer implementation which generates JSON files according to the Composer includes specification.
 *
 * This consists of a packages.json file and a number of includes files. The number of includes depends on the
 * diversity of the source entries and the partition function.
 *
 * This is suitable for larger repositories.
 *
 * @see https://getcomposer.org/doc/05-repositories.md#includes.
 */
class IncludesPacker extends Packer
{

    /**
     * @inheritDoc
     */
    public function generate()
    {
        $files = array();

        $partitions = $this->partition($this->source->getEntries());

        // Generate all the includes files which contain the actual packages.
        $includes = array();
        foreach ($partitions as $partition => $entries) {
            // Retrieve the packages for each partition.
            $packages = array();
            foreach ($this->source->getPackages($entries) as $package) {
                if (!isset($packages[$package->getName()])) {
                    $packages[$package->getName()] = array();
                }
                $packages[$package->getName()][$package->getVersion()] = $this->dumper->dump($package);
            }

            // Write the packages to a file.
            $fileName = 'packages-' . $partition . '.json';
            $file = new JsonFile($this->dir . $fileName);
            $file->write(array('packages' => $packages));
            $files[] = $file;

            // Add the file to an index of all includes.
            $includes['../' . $fileName] = array(
                'sha1' => sha1_file($file->getPath())
            );
        }

        // Finally write all the includes to the root packages.json file.
        $fileName = $this->dir . 'packages.json';
        $file = new JsonFile($fileName);
        $file->write(array('includes' => $includes));
        $files[] = $file;

        return $files;
    }
}
