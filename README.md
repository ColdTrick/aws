Amazon Web Services
===================

![Elgg 3.3](https://img.shields.io/badge/Elgg-3.3-green.svg)
[![Build Status](https://scrutinizer-ci.com/g/ColdTrick/aws/badges/build.png?b=master)](https://scrutinizer-ci.com/g/ColdTrick/aws/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ColdTrick/aws/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ColdTrick/aws/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/coldtrick/aws/v/stable.svg)](https://packagist.org/packages/coldtrick/aws)
[![License](https://poser.pugx.org/coldtrick/aws/license.svg)](https://packagist.org/packages/coldtrick/aws)

A wrapper plugin for access to the Amazon Web Service

Developers
----------

To automaticly upload files to AWS S3 configure the S3 bucket settings and register the subtype of your entities with the plugin hook
`upload:subtypes`, `aws:s3`. This plugin hook should return an array of subtypes which implement `ElggFile`.

There is a generic `ElggFile` delete event listener which will remove any uploaded file from AWS S3 if it get removed from the community.
