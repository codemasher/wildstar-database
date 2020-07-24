# wildstar-database

[![version][packagist-badge]][packagist]
[![license][license-badge]][license]
[![Scrunitizer][scrutinizer-badge]][scrutinizer]
[![PayPal donate][donate-badge]][donate]

[packagist-badge]: https://img.shields.io/packagist/v/codemasher/wildstar-database.svg?style=flat-square
[packagist]: https://packagist.org/packages/codemasher/wildstar-database
[license-badge]: https://img.shields.io/github/license/codemasher/wildstar-database.svg?style=flat-square
[license]: https://github.com/codemasher/wildstar-database/blob/master/LICENSE
[scrutinizer-badge]: https://img.shields.io/scrutinizer/g/codemasher/wildstar-database.svg?style=flat-square
[scrutinizer]: https://scrutinizer-ci.com/g/codemasher/wildstar-database
[donate-badge]: https://img.shields.io/badge/donate-paypal-ff33aa.svg?style=flat-square
[donate]: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=WLYUNAT9ZTJZ4

# Requirements 
(if not running Vagrant)
- the WildStar game client (non-Steam)
- PHP 7.2+
  - the [XZ (LZMA2) extension](https://github.com/codemasher/php-ext-xz) if you want to extract the game files (currently linux/Vagrant only)
  - the [Imagick](https://github.com/Imagick/imagick) extension and [imagemagick](https://imagemagick.org/) running on the host if you want to create map tiles
- a MySQL or Postgres database
- [Wildstar Studio](https://bitbucket.org/mugadr_m/wildstar-studio/downloads/)

## Running the Vagrant box
### Requirements:
- [Vagrant](https://www.vagrantup.com/downloads.html) (v2.2.x+)
  - the [bionic64 box](https://app.vagrantup.com/ubuntu/boxes/bionic64)
  - [vagrant-reload](https://github.com/aidanns/vagrant-reload)
  - [vagrant-landrush](https://github.com/vagrant-landrush/landrush)
