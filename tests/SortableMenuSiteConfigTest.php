<?php

namespace Symbiote\SortableMenu\Tests;

use Page;

use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Core\Config\Config;
use Symbiote\SortableMenu\SortableMenuExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\FunctionalTest;

class SortableMenuSiteConfigTest extends FunctionalTest
{
    protected static $use_draft_site = true;

    protected $usesDatabase = true;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        // NOTE(Jake): 2018-08-13
        //
        // Add configs to SortableMenuExtension, then apply to
        // `Page` record. Because this modifies the DB fields, we need
        // to call `static::resetDBSchema(true, true);`
        //
        Config::modify()->set(SortableMenuExtension::class, 'menus', array(
            'ShowInFooter' => array(
                'Title' => 'Footer',
            ),
            'ShowInSidebar' => array(
                'Title' => 'Sidebar',
            ),
        ));
        Page::add_extension(SortableMenuExtension::class);
        static::resetDBSchema(true, true);
    }

    public function setUp()
    {
        parent::setUp();
        // NOTE(Jake): 2018-08-10
        //
        // If we can't find "SiteConfig" class, skip all tests.
        //
        if (!class_exists(SiteConfig::class)) {
            $this->markTestSkipped(sprintf('Skipping %s ', static::class));
            return;
        }
    }

    public function testLoadEditingSiteConfigWithNoData()
    {
        $this->logInWithPermission('ADMIN');

        // Taken from SiteConfig::requireDefaultRecords()
        $config = DataObject::get_one(SiteConfig::class);
        if (!$config) {
            $config = SiteConfig::make_site_config();
        }

        $response = $this->get($config->CMSEditLink());

        // Check that the GridFields have been created in the CMS
        $this->assertContains('<input type="hidden" name="ShowInFooter[GridState]" ', $response->getBody());
        $this->assertContains('<input type="hidden" name="ShowInSidebar[GridState]" ', $response->getBody());
    }

    /**
     * Taken from "framework\tests\view\SSViewerTest.php"
     */
    private function assertEqualIgnoringWhitespace($a, $b, $message = '')
    {
        $this->assertEquals(preg_replace('/\s/', '', $a), preg_replace('/\s/', '', $b), $message);
    }
}
