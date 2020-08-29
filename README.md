# PHP V

A proof-of-concept external mod menu for GTA V written in PHP using [FFI](https://www.php.net/manual/en/book.ffi.php) to interface with a custom C++ DLL to read & write process memory and [php-gui](https://github.com/gabrielrcouto/php-gui).

![Screenshot](screenshot.png)

## Using it

### The easy way

1. Download `PHP-V.zip` from [the latest release](https://github.com/Sainan/PHP-V/releases).
2. Extract the zip.
3. Double-click `start.bat`.

### The masochist way

1. Ensure that you have PHP >= 7.4 installed.
2. Clone this repo.
3. Compile `src/cpp_api.cpp` to create a `bin/cpp_api.dll`. If you have [MinGW-W64](https://sourceforge.net/projects/mingw-w64/files/), you can use `compile-dll-with-g++.bat`.
4. `composer install`.
5. `php -d extension=ffi run.php`.
