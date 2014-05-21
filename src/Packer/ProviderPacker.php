<?php

namespace Reload\Repack\Packer;

use Composer\Json\JsonFile;

/**
 * A Packer implementation which generates JSON files according to the Composer provider-includes/providers-url
 * specification.
 *
 * This consists of a packages.json files, a number of includes files which contain package names and a file for
 * each package. An url scheme in the packages.json file map package names to files.
 *
 * This is suitable for very large repositories like http://packagist.org.
 *
 * @see https://getcomposer.org/doc/05-repositories.md#provider-includes-and-providers-url
 */
class ProviderPacker extends Packer
{

    /**
     * @inheritDoc
     */
    public function generate()
    {
        $files = array();

        // Setup basic entries for the packages.json file.
        $includes = array(
            'packages' => array(),
            'provider-includes' => array(),
            'providers-url' => '../packages/%package%$%hash%.json'
        );

        // Generate files for individual packages and include files for partitions.
        $partitions = $this->partition($this->source->getEntries());
        foreach ($partitions as $partition => $entries) {
            $providers = array();
            foreach ($entries as $entry) {
                // Generate package file.
                $package = $this->source->getPackage($entry);
                $packages = array('packages' => array($this->dumper->dump($package)));
                $hash = hash('sha256', JsonFile::encode($packages));

                // The path to the file is dictated by the providers url.
                $replacements = array(
                    '../' => $this->dir,
                    '%package%' => $package->getName(),
                    '%hash%' => $hash,
                );
                $file = new JsonFile(
                    str_replace(
                        array_keys($replacements),
                        array_values($replacements),
                        $includes['providers-url']
                    )
                );
                $file->write($packages);
                $files[] = $file;

                // Finally add information about the package to the include file.
                $providers[$package->getName()] = array('sha256' => $hash);
            }

            // Generate the include file for the partition.
            $fileName = 'packages-' . $partition . '.json';
            $file = new JsonFile($this->dir . $fileName);
            $file->write(array('providers' => $providers));
            $files[] = $file;

            // Add the include files to the packages.json file
            $includes['provider-includes']['../' . $fileName] = array(
                'sha256' => hash_file('sha256', $file->getPath())
            );
        }

        // Finally generate the root packages.json file.
        $file = new JsonFile($this->dir . 'packages.json');
        $file->write($includes);
        $files[] = $file;

        return $files;
    }
}
