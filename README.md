# Repack - static Composer repository generator

Repack is a small library for repackaging a source of PHP-related packages as a Composer repository. The library generates static [Composer repositories](https://getcomposer.org/doc/05-repositories.md) - `packages.json` files and any other files needed.

The goals of the project are to:

* Provide a well-defined API for generating a repository instead of building one giant array structure.
* Make it easy to switch between [different types of repositories](https://getcomposer.org/doc/05-repositories.md#composer) to match the content of the repository.

To use it you need to provide a subclass of `Reload\Repack\Source\Source` to provide imformation about what packages are available and how they should be treated.

## Usage

To generate a simple `packages.json` file containing all Composer packages in a source:

```php
$source = new \Custom\Source();
$dir = "/some/output/directory";
$packer = new \Reload\Repack\SimplePacker($source, $dir);
$packer->generate();
```

If the number of packages in the source grows beyond what is recommendable for a single `packages.json` file the `SimplePacker` can transparently be replaced by another subclass of `Reload\Repack\Packer\Packer` such as the `IncludesPacker` or the `ProviderPacker`.

## Source implementation

The `Reload\Repack\Source\Source` subclass is responsible for a lot of the heavy lifting in the generation process. It must implement at least two methods:

* `getEntries()`: This method is responsible for retrieving a list of entries which are to be converted to a Composer package. The data type of an entry is up to the subclass. The `Packer` does not call it directly. It could the response from an API listing call or a list of ids from a database table. 
* `getPackage($entry)`: This method is responsible for converting a single entry to a corresponding `\Composer\Package\Package` object.

The source may choose to implement one more method:

* `getPartition($entry)`: Returns a partition name for an entry in the form of a string. If a source implementation chooses to implement this method then the `Packer` can split the packages into smaller chunks. Entries which return the same partition will end up in the same chunk. This can optimize the way Composer loads the repository.

## License

The code is released under the MIT license.
