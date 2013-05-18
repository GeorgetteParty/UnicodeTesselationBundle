UnicodeTesselationBundle
========================

Provides a service for reading / writing ASCII or Unicode maps / grids with square or hexagonal tiles.


How to Install
==============

1. Download Composer.

2. Install

```
  $ composer.phar install
```


How to Test
===========

Create and configure your `phpunit.xml` from `phpunit.xml.dist`.

```
  $ phpunit
```


Squares
=======

The driver can read / write to any rectangle-shaped square grid, such as :

```
  +---+
  | A |
  +---+

  +---+
  | A |
  +---+
  | B |
  +---+

  +---+---+
  | A | B |
  +---+---+

  +-------+-------+
  |       |       |
  | LUCKY |  777  |
  |       |       |
  +-------+-------+
  | EARTH |       |
  |   &   | 1234  |
  | VENUS |       |
  +-------+-------+

  +---+---+---+
  | A | B | C |
  +---+---+---+
  | D | E | F |
  +---+---+---+

```

It reads rows and then columns.
Look into [the tests](Tests/Driver/AsciiSquareTest.php) for the exact specifications of the PHP arrays.



Hexagons
========


The driver can read / write to some hexagonal grids.

```
    _____
   /     \
  /   A   \
  \       /
   \_____/


           _____
          /     \
    _____/   A   \_____
   /     \       /     \
  /   B   \_____/   F   \
  \       /     \       /
   \_____/   O   \_____/
   /     \       /     \
  /   C   \_____/   E   \
  \       /     \       /
   \_____/   D   \_____/
         \       /
          \_____/


```

It will choose an hexagon to be the origin in a barycentric coordinate system.
It tries to assert which hexagon is the most appropriate to be the origin.

The PHP array will be constructed as to give each hexagon barycentric coordinates.

Look into [the tests](Tests/Driver/AsciiHexagonTest.php) for the exact specifications of the PHP arrays.





Caveats
=======

```php
  // You will need this if you use Unicode chars
  mb_internal_encoding('UTF-8');
```