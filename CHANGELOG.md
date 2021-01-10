# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.1.0 - 2021-01-10

### Added

- [#6](https://github.com/mezzio/mezzio-authentication-laminasauthentication/pull/6) Adds PHP 8.0 support


-----

### Release Notes for [1.1.0](https://github.com/mezzio/mezzio-authentication-laminasauthentication/milestone/1)



### 1.1.0

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

 - [6: Added PHP 8.0 Support](https://github.com/mezzio/mezzio-authentication-laminasauthentication/pull/6) thanks to @delassiter

## 1.0.1 - 2019-06-18

### Added

- [zendframework/zend-expressive-authentication-zendauthentication#10](https://github.com/zendframework/zend-expressive-authentication-zendauthentication/pull/10)
  adds PHP 7.3 support

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.0 - 2018-12-18

### Added

- [zendframework/zend-expressive-authentication-zendauthentication#9](https://github.com/zendframework/zend-expressive-authentication-zendauthentication/pull/9) updates the component to implement the mezzio-authentication 1.0 interfaces.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-authentication-zendauthentication#7](https://github.com/zendframework/zend-expressive-authentication-zendauthentication/pull/7) avoids `initiateAuthentication` call on any forms with POST method behind authentication.

## 0.4.0 - 2018-03-15

### Added

- Adds support for mezzio-authentication 0.4.0 and up.

### Changed

- [zendframework/zend-expressive-authentication-zendauthentication#5](https://github.com/zendframework/zend-expressive-authentication-zendauthentication/pull/5)
  changes the constructor of the `Mezzio\Authentication\LaminasAuthentication\LaminasAuthentication`
  class to accept a callable `$responseFactory` instead of a
  `Psr\Http\Message\ResponseInterface` response prototype. The
  `$responseFactory` should produce a `ResponseInterface` implementation when
  invoked.

- [zendframework/zend-expressive-authentication-zendauthentication#5](https://github.com/zendframework/zend-expressive-authentication-zendauthentication/pull/5)
  updates the `LaminasAuthenticationFactory` to no longer use
  `Mezzio\Authentication\ResponsePrototypeTrait`, and instead always
  depend on the `Psr\Http\Message\ResponseInterface` service to correctly return
  a PHP callable capable of producing a `ResponseInterface` instance.

### Deprecated

- Nothing.

### Removed

- Removes support for releases of mezzio-authentication prior to 0.4.0.

### Fixed

- Nothing.

## 0.3.0 - 2018-02-26

### Added

- [zendframework/zend-expressive-authentication-zendauthentication#3](https://github.com/zendframework/zend-expressive-authentication-zendauthentication/pull/3)
  adds support for the 0.3 release of mezzio-authentication.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-expressive-authentication-zendauthentication#3](https://github.com/zendframework/zend-expressive-authentication-zendauthentication/pull/3)
  removes support for the 0.2 release of mezzio-authentication.

### Fixed

- Nothing.

## 0.2.1 - 2017-12-13

### Added

- [zendframework/zend-expressive-authentication-zendauthentication#1](https://github.com/zendframework/zend-expressive-authentication-zendauthentication/pull/1)
  adds support for the 1.0.0-dev branch of mezzio-authentication.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.2.0 - 2017-11-28

### Added

- Adds support for mezzio-authentication 0.2.0.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Adds support for mezzio-authentication 0.1.0.

### Fixed

- Nothing.

## 0.1.0 - 2017-11-09

Initial release.

### Added

- Everything.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
