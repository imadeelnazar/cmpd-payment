<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit1af91f0de70fd7c7b624213480ba5458
{
    public static $prefixLengthsPsr4 = array (
        'a' => 
        array (
            'apimatic\\jsonmapper\\' => 20,
        ),
        'U' => 
        array (
            'Unirest\\' => 8,
        ),
        'S' => 
        array (
            'Stripe\\' => 7,
            'Sample\\' => 7,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
            'PaypalServerSdkLib\\' => 19,
            'PayPalHttp\\' => 11,
            'PayPalCheckoutSdk\\' => 18,
        ),
        'C' => 
        array (
            'Core\\' => 5,
            'CoreInterfaces\\' => 15,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'apimatic\\jsonmapper\\' => 
        array (
            0 => __DIR__ . '/..' . '/apimatic/jsonmapper/src',
        ),
        'Unirest\\' => 
        array (
            0 => __DIR__ . '/..' . '/apimatic/unirest-php/src',
        ),
        'Stripe\\' => 
        array (
            0 => __DIR__ . '/..' . '/stripe/stripe-php/lib',
        ),
        'Sample\\' => 
        array (
            0 => __DIR__ . '/..' . '/paypal/paypal-checkout-sdk/samples',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/src',
        ),
        'PaypalServerSdkLib\\' => 
        array (
            0 => __DIR__ . '/..' . '/paypal/paypal-server-sdk/src',
        ),
        'PayPalHttp\\' => 
        array (
            0 => __DIR__ . '/..' . '/paypal/paypalhttp/lib/PayPalHttp',
        ),
        'PayPalCheckoutSdk\\' => 
        array (
            0 => __DIR__ . '/..' . '/paypal/paypal-checkout-sdk/lib/PayPalCheckoutSdk',
        ),
        'Core\\' => 
        array (
            0 => __DIR__ . '/..' . '/apimatic/core/src',
        ),
        'CoreInterfaces\\' => 
        array (
            0 => __DIR__ . '/..' . '/apimatic/core-interfaces/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'R' => 
        array (
            'Rs\\Json' => 
            array (
                0 => __DIR__ . '/..' . '/php-jsonpointer/php-jsonpointer/src',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit1af91f0de70fd7c7b624213480ba5458::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit1af91f0de70fd7c7b624213480ba5458::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit1af91f0de70fd7c7b624213480ba5458::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit1af91f0de70fd7c7b624213480ba5458::$classMap;

        }, null, ClassLoader::class);
    }
}
