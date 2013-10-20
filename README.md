# php-jiggle [![Latest Stable Version](https://poser.pugx.org/holgerk/jiggle/v/stable.png)](https://packagist.org/packages/holgerk/jiggle) [![Build Status](https://travis-ci.org/holgerk/php-jiggle.png?branch=master)](https://travis-ci.org/holgerk/php-jiggle)

Jiggle is a depency injection container for php 5.3+

## Examples

<!-- START AUTOGENERATED EXAMPLES -->
### Set and get dependencies
```php
$jiggle = new Jiggle;
$jiggle->d1 = 42;
echo $jiggle->d1; // => 42
```

### Lazy loading with factory functions
```php
$jiggle = new Jiggle;
$jiggle->d1 = function() {
    return 42;
};
echo $jiggle->d1; // => 42
```

### Basic wiring of dependencies
```php
$jiggle = new Jiggle;
$jiggle->d1 = 42;
$jiggle->d2 = function() use($jiggle) {
    return $jiggle->d1;
};
echo $jiggle->d2; // => 42
```

### Magic injection of depencies into factory functions
```php
$jiggle = new Jiggle;
$jiggle->d1 = 42;
$jiggle->d2 = function($d1) {
    return $d1;
};
echo $jiggle->d2; // => 42
```

### Basic instantiation
```php
$jiggle = new Jiggle;
$jiggle->d1 = 40;
$jiggle->d2 = 2;
$jiggle->d3 = function() use($jiggle) {
    return new D3($jiggle->d1, $jiggle->d2);
};
echo $jiggle->d3->sum(); // => 42
```

### Instantiation with magic constructor injection
```php
$jiggle = new Jiggle;
$jiggle->d1 = 40;
$jiggle->d2 = 2;
$jiggle->d3 = function() use($jiggle) {
    return $jiggle->create('D3');
};
echo $jiggle->d3->sum(); // => 42
```

### Short form of magic constructor injection
```php
$jiggle = new Jiggle;
$jiggle->d1 = 40;
$jiggle->d2 = 2;
$jiggle->d3 = $jiggle->createFactory('D3');
echo $jiggle->d3->sum(); // => 42
```

### Basic function dependency
```php
$jiggle = new Jiggle;
$jiggle->d1 = function() {
    return function() {
        return 42;
    };
};
echo $jiggle->d1(); // => 42
```


<!-- END AUTOGENERATED EXAMPLES -->

## Composer
```json
{
    "require": {
        "holgerk/jiggle": "*"
    }
}
```

## License

The MIT License (MIT)

Copyright (c) 2013 Holger Kohnen

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
