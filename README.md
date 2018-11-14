# Toggle (Simplify)

[![Build Status](https://travis-ci.com/MilesChou/toggle-simplify.svg?branch=master)](https://travis-ci.com/MilesChou/toggle-simplify)
[![codecov](https://codecov.io/gh/MilesChou/toggle-simplify/branch/master/graph/badge.svg)](https://codecov.io/gh/MilesChou/toggle-simplify)
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
$toggle->feature('f1')['params']['name'];

// Also using in callback
$toggle->create('f3', function($context, array $params) {
    return $params['key'] === $context['key'];
}, ['key' => 'foo']);
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
