Most or all Drupal behaviours should work as expected, but if you're
encountering errors or strange issues with behaviours you've written, we
recommend the following:

* They should have detach callbacks that fully clean up changes their attach callbacks make, restoring elements on the page as if the attach was never applied.

* Don't store references to elements outside of behaviour callbacks unless you absolutely have to. They can and will get out of sync with the DOM, resulting in references to elements no longer on the page.
