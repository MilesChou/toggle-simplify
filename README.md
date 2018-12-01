# Toggle (Simplify)

[![Build Status](https://travis-ci.com/MilesChou/toggle-simplify.svg?branch=master)](https://travis-ci.com/MilesChou/toggle-simplify)
[![Coverage Status](https://coveralls.io/repos/github/MilesChou/toggle-simplify/badge.svg)](https://coveralls.io/github/MilesChou/toggle-simplify)
[![Latest Stable Version](https://poser.pugx.org/MilesChou/toggle-simplify/v/stable)](https://packagist.org/packages/MilesChou/toggle-simplify)
[![Total Downloads](https://poser.pugx.org/MilesChou/toggle-simplify/d/total.svg)](https://packagist.org/packages/MilesChou/toggle-simplify)
[![License](https://poser.pugx.org/MilesChou/toggle-simplify/license)](https://packagist.org/packages/MilesChou/toggle-simplify)

The simplify feature toggle library for PHP

## Concept

Coming soon...

## Usage

Just one file! Using the `Toggle` to do everything.

### Feature Toggle

Use the fixed result:

```php
<?php

$toggle = new Toggle();
$toggle->create('f1', true);

// Will return true
$toggle->isActive('f1');
```

Use callable to decide the return dynamically:

```php
<?php

$toggle = new Toggle();
$toggle->create('f1', function() {
    return true;
});

// Will return true
$toggle->isActive('f1');
```

Use callable with Context:

```php
<?php

$toggle = new Toggle();
$toggle->create('f1', function($context) {
    return $context['default'];
});

// Will return true
$toggle->isActive('f1', [
    'return' => true,
]);
```

### Parameters

`Feature` instance can store some parameter. For example:

```php
<?php

$toggle = new Toggle();

$toggle->create('f1', true, ['name' => 'Miles']);
$toggle->create('f2', false, ['name' => 'Chou']);

// Will return 'Chou'
$toggle->params('f1', 'name');

// Also using in callback
$toggle->create('f3', function($context, array $params) {
    return $params['key'] === $context['key'];
}, ['key' => 'foo']);
```

### Export / Import result

When you want persistent the result to some storage, we can use the `result()` method.

```php
<?php

$toggle = new Toggle();

$toggle->create('f1', true);
$toggle->create('f2', false);

$result = $toggle->result(); // array ['f1' => true, 'f2' => false]
```

Also, you can restore the result.

```php
<?php

$toggle = new Toggle();

$toggle->create('f1', false);
$toggle->create('f2', true);
$toggle->result(['f1' => true, 'f2' => false]);

$toggle->isActive('f1'); // true
$toggle->isActive('f2'); // false
```

### Control Structure

This snippet is like `if` / `switch` structure:

```php
<?php

$toggle = new Toggle();
$toggle->create('f1');
$toggle->create('f2');
$toggle->create('f3');

$toggle
    ->when('f1', function ($context, $params) {
        // Something when f1 is on
    })
    ->when('f2', function ($context, $params) {
        // Something when f2 is on
    })
    ->when('f3', function ($context, $params) {
        // Something when f3 is on
    });
```
