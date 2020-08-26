# PHP GTA External

A proof-of-concept external trainer/mod menu for GTA V written in PHP using FFI to interface with a custom C++ DLL.

## Dependencies

- PHP-CLI >= 7.4 — the easiest way to get this is to just [get Cone](https://getcone.org) which will install PHP-CLI with itself.
- PHP FFI Extension — Head into your `%ProgramFiles%` folder, find the "PHP" folder, open the `php.ini` in your favourite text editor, and add `extension=ffi` at the end. 

### The DLL

Because not all functionality needed is available in standard PHP, FFI is used to interface with `bin/cpp_api.dll`, which was compiled from `src/cpp_api.cpp`. If it makes you feel any better, you can use your favourite compiler to compile it yourself.

## What it can do

- Turn you invisible ([as seen in the showcase video](https://www.youtube.com/watch?v=a_XwK-G3Bfg))
    - `php invisible-on.php`
    - `php invisible-off.php`
- Show your position
    - `php print-pos.php`
- Detect your edition (Steam, Social Club, or Epic Games)
    - `php edition.php`
