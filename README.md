SimpleZip
=========

SimpleZip is fast and format-conforming PHP library to create ZIP archives.

For now it's just a small class from one of my projects, it has not been widely tested and ::create() function does not check input for all error cases. Please report bugs, suggestions and feature requests to [offshore@aopdg.ru]()

Features
========

Feature            | ZipArchive    | SimpleZip
------------------ |:-------------:|:-------------:
create archives    | yes           | yes
update archives    | yes           | no
extract archives   | yes           | no
utf8 file names    | no            | yes
create without temporary outfile | no | yes
create files in one pass | no | yes
support adding files larger than php allowed memory | no | yes
global compression method selection | no | yes
per-file compression method selection | no | yes


Usage
=====
```php
require_once 'SimpleZip.php'; // or autoload if you will

$files = array (
    'LICENSE.txt' => 'LICENSE', // note different names
    'README.md' => array(
        'src' => 'README.md',
        'info' => 'Test file info',
        'method' => SimpleZip::CM_DEFLATE,
    )
);
$opts = array(
    'info' => 'Test global info',
    'method' => SimpleZip::CM_STORE,
);
if (!SimpleZip::create('test.zip', $files, $opts, $error)) {
    echo "Error: $error\n";
}

```
