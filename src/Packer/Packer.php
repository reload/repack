<?php


namespace Reload\Repack\Packer;

use Composer\Json\JsonFile;
use Composer\Package\Dumper\ArrayDumper;
use Reload\Repack\Source\Source;

/**
 * Base class for all packers.
 *
 * A packer implementation is responsible for given a source then generating files representing a valid Composer
 * repository.
 */
abstract class Packer
{

    /**
     * The source from which package information will be retrieved.
     *
     * @var Source
     */
    protected $source;

    /**
     * The base directory where the Composer repository files will be generated.
     *
     * @var string
     */
    protected $dir;

    /**
     * Class for converting Composer Package classes to arrays which can be written to files.
     *
     * @var ArrayDumper
     */
    protected $dumper;

    public function __construct(Source $source, $outputDir)
    {
        $this->source = $source;
        $this->dir = $outputDir . DIRECTORY_SEPARATOR;
        $this->dumper = new ArrayDumper();
    }

    /**
     * Generate files for representing the source as a Composer repository according to the type of packer.
     *
     * @return JsonFile[] The JSON files used to represent the Composer repository.
     */
    abstract public function generate();

    /**
     * Split source entries into smaller partitions according to the partition function provided by the source.
     *
     * @param array $entries The source entries to split
     * @return array[] An array of source entries split into smaller partitions keyed by partition name.
     */
    protected function partition(array $entries)
    {
        $partitions = array();
        foreach ($entries as $entry) {
            $partition = $this->source->getPartition($entry);
            if (empty($partitions[$partition])) {
                $partitions[$partition] = array();
            }
            $partitions[$partition][] = $entry;
        }

        return $partitions;
    }
}
