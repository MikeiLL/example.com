# Change Log
All notable changes to this project will be documented in this file, formatted via [this recommendation](https://keepachangelog.com/).

## [2.5.0] - 2021-03-31
### Added
- New "Switch Accounts" link in the addon settings to change the Stripe account used.
- Filter to apply custom styles to a Credit Card field.
- Email Notifications option to limit to completed payments only.

### Changed
- Full object is now logged instead of just a message in case of a Stripe error.
- Upgrade Stripe PHP SDK to 7.72.0.

### Fixed
- Edge case when a subscription email is still being required despite changing the payment to single by conditional logic.
- Invalid characters in 'font-family' added by external CSS rules may break the Credit Card field.
- Stripe form with active Captcha fails to submit after Stripe 3DSecure validation.

## [2.4.3] - 2020-12-17
### Fixed
- Stripe Live/Test modal appears when clicking on any checkbox in WPForms settings while using jQuery 3.0.

## [2.4.2]
### Changed
- Improved the error rate limiting by adding file-based rate-limiting log storage.

## [2.4.1] - 2020-08-06
### Fixed
- Card field can be mistakenly processed as hidden under some conditional logic configurations.

## [2.4.0] - 2020-08-05
### Added
- Stripe Elements locale can be set explicitly via the filter.

### Changed
- Improved Stripe error handling during form processing.

### Fixed
- Conditionally hidden Stripe field should not be processed on form submission.

## [2.3.4] - 2020-04-30
### Fixed
- In some edge cases Stripe payment goes through without creating a form entry.

## [2.3.3] - 2020-01-15
### Fixed
- Payment form entry details are not updated despite Stripe payment completing successfully.

## [2.3.2] - 2020-01-09
### Changed
- Improved form builder messaging when Stripe plugin settings have not been configured.
- Improved messaging on Stripe plugin settings.

## [2.3.1] - 2019-10-14
### Fixed
- PHP notice in WPForms settings if user has no Stripe forms.
- Stripe Connect issues switching between Live/Test mode.

## [2.3.0]
### Added
- SCA support.
- Stripe Elements.
- Stripe Connect.
- Rate limiting for failed payments.

## [2.2.0] - 2019-07-23
### Added
- Complete translations for French and Portuguese (Brazilian).

## [2.1.2] - 2018-03-11
### Changed
- Stripe API key settings display order, to follow Stripe documentation.

## [2.1.1] - 2018-02-08
### Fixed
- Typos, grammar, and other i18n related issues.

## [2.1.0] - 2018-02-06
### Added
- Complete translations for Spanish, Italian, Japanese, and German.

### Changed
- Processing now checks to make sure order amount is above Stripe minimum (0.50) before proceeding.

### Fixed
- Typos, grammar, and other i18n related issues.

## [2.0.2] - 2018-11-27
### Added
- Include addon information when connecting to Stripe API.

## [2.0.1] - 2018-09-05
### Fixed
- Stripe API error

## [2.0.0] - 2018-09-04
### IMPORTANT
- The addon structure has been improved and refactored. If you are extending the plugin by accessing the class instance, an update to your code will be required before upgrading (use `wpforms_stripe()`).

### Added
- Recurring subscription payments! 💥🎉

### Changed
- Improved metadata sent with charge details.

### Removed
- `wpforms_stripe_instance` function and `WPForms_Stripe::instance()`.

## [1.1.3] - 2018-05-14
### Changed
- Enable Credit Card field when addon is activated; as of WPForms 1.4.6 the credit card field is now disabled/hidden unless explicitly enabled.

## [1.1.2] - 2018-04-05
### Changed
- Improved enforcement of Stripe processing with required credit card fields.

## [1.1.1] - 2017-08-24
### Changed
- Remove JS functionality adopted in core plugin

## [1.1.0] - 2017-06-13
### Changed
- Use settings API for WPForms v1.3.9.

## [1.0.9] - 2017-08-01
### Changed
- Improved performance when checking for credit card fields in the form builder

## [1.0.8] - 2017-03-30
### Changed
- Updated Stripe API PHP library
- Improved Stripe class instance accessibility

## [1.0.7] - 2017-01-17
### Changed
- Check for charge object before firing transaction completed hook

## [1.0.6] - 2016-12-08
### Added
- Support for Dropdown Items payment field
- New hook for completed transactions, `wpforms_stripe_process_complete`
- New filter stored credit card information, `wpforms_stripe_creditcard_value`

## [1.0.5] - 2016-10-07
### Fixed
- Javascript processing method to avoid conflicts with core duplicate submit prevention feature

## [1.0.4] - 2016-08-22
### Added
- Expanded support for additional currencies

### Fixed
- Localization issues/bugs

### Changed

## [1.0.3] - 2016-07-07
### Added
- Conditional logic for payments

### Changed
- Improved error logging

## [1.0.2] - 2016-06-23
### Changed
- Prevent plugin from running if WPForms Pro is not activated

## [1.0.1] - 2016-04-01
### Fixed
- PHP notices with some configurations

## [1.0.0] - 2016-03-28
### Added
- Initial release
