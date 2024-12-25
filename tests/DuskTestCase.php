<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        if (! static::runningInSail()) {
            static::startChromeDriver();
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--headless=new',
            '--window-size=1920,1080',
        ]);

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY,
                $options
            )
        );
    }

    /**
     * Capture a Percy snapshot.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  string  $name
     * @param  array  $options
     * @return void
     */
    protected function percy($browser, $name, array $options = [])
    {
        if (env('PERCY_ENABLED', false)) {
            $defaultOptions = [
                'widths' => [375, 768, 1280],
                'minHeight' => 1024,
                'enableJavaScript' => true,
            ];

            $options = array_merge($defaultOptions, $options);

            // Attendre que la page soit complètement chargée
            $browser->waitFor('body');
            
            // Masquer les éléments dynamiques
            $browser->script("
                document.querySelectorAll('[data-testid=\"timestamp\"]')
                    .forEach(el => el.style.visibility = 'hidden');
                document.querySelectorAll('[data-testid=\"random-data\"]')
                    .forEach(el => el.style.opacity = '0');
            ");

            // Capturer la snapshot
            $browser->percySnapshot($name, $options);
        }
    }

    /**
     * Capture une snapshot Percy avec un état spécifique.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  string  $name
     * @param  string  $state
     * @return void
     */
    protected function percyState($browser, $name, $state)
    {
        $this->percy($browser, "{$name}-{$state}");
    }

    /**
     * Capture des snapshots Percy pour différentes tailles d'écran.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  string  $name
     * @return void
     */
    protected function percyResponsive($browser, $name)
    {
        $sizes = [
            'mobile' => [375, 812],
            'tablet' => [768, 1024],
            'desktop' => [1280, 1024],
        ];

        foreach ($sizes as $device => [$width, $height]) {
            $browser->resize($width, $height);
            $this->percy($browser, "{$name}-{$device}");
        }
    }
}
