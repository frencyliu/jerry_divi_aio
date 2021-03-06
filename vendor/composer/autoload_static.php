<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9571488973ec4f6f17f0b2881d307d14
{
    public static $prefixLengthsPsr4 = array (
        'O' => 
        array (
            'ODS\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'ODS\\' => 
        array (
            0 => __DIR__ . '/..' . '/oberonlai/wp-metabox/src',
            1 => __DIR__ . '/..' . '/oberonlai/wp-option/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9571488973ec4f6f17f0b2881d307d14::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9571488973ec4f6f17f0b2881d307d14::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit9571488973ec4f6f17f0b2881d307d14::$classMap;

        }, null, ClassLoader::class);
    }
}
