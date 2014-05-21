<?php

namespace Reload\Repack\Source;

use Composer\Package\Package;

/**
 * A Source implementation is a collection of entries where each entry should be conversed to a Composer package.
 *
 * The process consists of two steps:
 * - Retrieving all entries
 * - Converting a single entry into a package
 *
 * An entry is a representation of an entity which should be converted to a Composer package in  a simple format like an
 * id. This allows a Packer to deal with a subset of all entries at a time. This is especially useful for larger
 * sources as memory consumption is reduced.
 *
 * An implementation may also implement an partitioning algorithm which will divide all the entries into smaller groups.
 * This is useful for sources containing many entries combined with Packer implementations which split packages into
 * multiple files.
 */
abstract class Source
{

    /**
     * Retrieve all the entries contained by the source.
     *
     * @return array All entries.
     */
    abstract public function getEntries();

    /**
     * Get a Composer package for an entry.
     *
     * The data type of the entry will be the same as the entries returned by the getEntries() implementation.
     *
     * @param $entry mixed The entry to convert.
     * @return Package A Composer package representing the entry.
     */
    abstract public function getPackage($entry);

    /**
     * Return a partition name for an entry. This is used by Packers which split packages into smaller groups.
     *
     * The data type of the entry will be the same as the entries returned by the getEntries() implementation.
     *
     * @param $entry mixed The entry to partition.
     * @return string The partition name for the entry.
     */
    public function getPartition($entry)
    {
        // The base implementation will place all entries in the same partition.
        return 0;
    }
}
