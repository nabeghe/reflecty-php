# Reflecty for PHP ≥ 7.4

> Simple reflection helper for PHP.

## 🫡 Usage

### 🚀 Installation

You can install the package via composer:

```bash
composer require nabeghe/reflecty
```

### Example - Class Ancestors

```php
use Nabeghe\Reflecty\Reflecty;

class Animal
{
}

class Lion extends Animal
{
}

class Cat extends Lion
{
}

print_r(Reflecty::classAncestors(Cat::class));

/*
    Array
    (
        [0] => Lion
        [1] => Animal
    )
 */
```

## 📖 License

Licensed under the MIT license, see [LICENSE.md](LICENSE.md) for details.