# PHP GTA External

A proof-of-concept external trainer/mod menu for GTA V written in PHP\*.

\* featuring some shady exe files written in C++ to extend PHP; I just couldn't be bothered to write a PHP extension.

## Dependencies

- PHP-CLI >=7.2 — the easiest way to get this is to just [get Cone](https://getcone.org) which will install PHP-CLI with itself.

Optionally, if you want to build the shady exe files yourself, get MinGW-W64 and run `./generate-shady-exe-files.sh` using Git Bash. Doing it this way was easiest for me, but you're free to do it your way, which I'm sure is better. ;)

## What it can do

- Turn you invisible ([as seen in the showcase video](https://www.youtube.com/watch?v=a_XwK-G3Bfg))
    - `php invisible-on.php`
    - `php invisible-off.php`
- Show your position
    - `php print-pos.php`
- Detect your edition (Steam, Social Club, or Epic Games)
    - `php edition.php` 
