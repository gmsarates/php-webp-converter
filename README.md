
# PHP WebP Converter

[PT] Esta é uma ferramenta CLI PHP para converter todos os arquivos em uma pasta específica para o formato webp

[EN] This is a PHP CLI tool to convert all files in a specific folder to webp format



## Install

Using composer:

```bash
  composer require gmsarates/php-webp-converter
```
    
## Usage

```bash
cd php-webp-converter/
```

```bash
php webp.php
```

## Optional parameters

| Param               | Info                                                |
| ----------------- | ---------------------------------------------------------------- |
| `--path` or `-P`       | Specify the path to the images |
| `--yes` or `-Y`       | Skip path checking |
| `--delete` or `-D`       | Skip verification to delete original files |

Example:

```bash
php webp.php --path=/var/www/html/project/images -Y -D
```

**Warning:** The `--delete` (or `-D`) parameter is dangerous! It will delete the original files. Use with caution.
