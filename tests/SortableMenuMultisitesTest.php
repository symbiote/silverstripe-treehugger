<?php

namespace Symbiote\SortableMenu\Tests;

use Page;

use Symbiote\Multisites\Model\Site;
use SilverStripe\Core\Config\Config;
use Symbiote\SortableMenu\SortableMenuExtension;
use SilverStripe\Dev\FunctionalTest;


//
// NOTE(Jake): 2018-08-10
//
// When Multisites is installed, this is the only test that can pass as
// the others get validation errors due to how Multisites works.
//
// At the time of writing, this test is only executed in TravisCI if
// the environment var `MULTISITES_VERSION` is set.
//
// Refer to .travis.yml and see when this is executed.
//
class SortableMenuMultisitesTest extends FunctionalTest
{
    protected static $use_draft_site = true;

    protected $usesDatabase = true;

    public function setUp()
    {
        // NOTE(Jake): 2018-08-09
        //
        // If we can't find "Site" class, skip all tests
        // in this class.
        //
        if (!class_exists(Site::class)) {
            $this->skipTest = true;
        }

        Config::inst()->update(SortableMenuExtension::class, 'menus', array(
            'ShowInFooter' => array(
                'Title' => 'Footer',
            ),
            'ShowInSidebar' => array(
                'Title' => 'Sidebar',
            ),
        ));
        // NOTE(Jake): 2018-08-09
        //
        // The core `$requiredExtensions` functionality isn't working here in SS 3.X.
        // I suspect its not flushing the YML or something?
        //
        Page::add_extension(SortableMenuExtension::class);
        parent::setUp();
    }

    public function testLoadEditingPageWithNoData()
    {
        $this->logInWithPermission('ADMIN');

        //
        $site = new Site();
        $site->Title = 'Test Site';
        $site->write();
        $site->doPublish();

        $pageID = $site->ID;
        $response = $this->get('admin/pages/edit/show/' . $pageID);

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
