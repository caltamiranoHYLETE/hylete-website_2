Development
===========

.. highlight:: bash

Run tests
---------

To run the unit tests you will need to have `PHPUnit <http://phpunit.de/>`_ installed and the module should be installed
in a local site.

To run the tests, call phpunit in the web root directory of the site::

    $ phpunit -c phpunit.vaimo_menu.xml.dist


Generate documentation
----------------------

The documentation is generated with a tool called `Sphinx <http://sphinx-doc.org/>`_ and is written in
`reStructuredText <http://sphinx-doc.org/rest.html>`_.

To install Sphinx run::

    $ pip install sphinx

To generate the documentation in html run::

    $ cd docs/
    $ make html

The documentation will be saved to docs/_build/html. To view the generated documentation open the index.html file in a
browser.