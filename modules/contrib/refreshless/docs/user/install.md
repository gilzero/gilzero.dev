Before attempting to install this module, we strongly recommend allowing
dependencies to patch Drupal core by running the following commands:

```shell
composer require 'cweagans/composer-patches:^1.7'
composer config extra.enable-patching true
composer config --json --merge extra.patchLevel '{"drupal/core": "-p2"}'
```

Then just `composer require` [the latest supported release of this
module](https://www.drupal.org/project/refreshless/releases) and core should be
patched; if patches were not attempted, you can often try running
`composer install` to give it a nudge to do so.

Finally, have Drupal install this module and it should take over handling page
requests.
