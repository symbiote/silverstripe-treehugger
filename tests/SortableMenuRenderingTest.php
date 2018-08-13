<?php

namespace Symbiote\SortableMenu\Tests;

use Page;
use SilverStripe\Core\Config\Config;
use Symbiote\SortableMenu\SortableMenuExtension;
use SilverStripe\Dev\FunctionalTest;

class SortableMenuRenderingTest extends FunctionalTest
{
    protected static $use_draft_site = true;

    protected $requireDefaultRecordsFrom = [
        Page::class,
    ];

    protected $usesDatabase = true;

    public function setUp()
    {
        Config::modify()->set(SortableMenuExtension::class, 'menus', array(
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
        //Config::modify()->set(Page::class, 'extensions', array(
        //    SortableMenuExtension::class,
        //));
        Page::add_extension(SortableMenuExtension::class);
        static::resetDBSchema(true, true);
        parent::setUp();
    }

    public function testRenderingMenusByOrder()
    {
        // Create footer items
        $record = new Page();
        $record->Title = 'Footer Menu Item #3';
        $record->ShowInFooter = true;
        $record->SortShowInFooter = 3;
        $record->write();

        $record = new Page();
        $record->Title = 'Footer Menu Item #1';
        $record->ShowInFooter = true;
        $record->SortShowInFooter = 1;
        $record->write();

        $record = new Page();
        $record->Title = 'Footer Menu Item #2';
        $record->ShowInFooter = true;
        $record->SortShowInFooter = 2;
        $record->write();

        // Get a page from DB - So we can call $SortableMenus() in the template.
        $record = Page::get()->filter(array('SortShowInFooter' => 1))->first();
        $this->assertNotNull($record);

        $expectedHTML = <<<HTML
<div class="footer">
    <ul class="footer-nav">
        <li>
            <a href="/footer-menu-item-1/">
                Footer Menu Item #1
            </a>
        </li>
        <li>
            <a href="/footer-menu-item-2/">
                Footer Menu Item #2
            </a>
        </li>
        <li>
            <a href="/footer-menu-item-3/">
                Footer Menu Item #3
            </a>
        </li>
    </ul>
</div>
HTML;
        $actualHTML = $record->renderWith(array(['type' => 'Includes', 'TestRenderingMenusByOrder']))->forTemplate();
        $this->assertEqualIgnoringWhitespace($expectedHTML, $actualHTML);
    }

    public function testPassInMenuList()
    {
        // Create footer items
        $record = new Page();
        $record->Title = 'Sidebar Menu Item #3';
        $record->ShowInSidebar = true;
        $record->SortShowInSidebar = 3;
        $record->write();

        $record = new Page();
        $record->Title = 'Sidebar Menu Item #1';
        $record->ShowInSidebar = true;
        $record->SortShowInSidebar = 1;
        $record->write();

        $record = new Page();
        $record->Title = 'Sidebar Menu Item #2';
        $record->ShowInSidebar = true;
        $record->SortShowInSidebar = 2;
        $record->write();

        // Get a page from DB - So we can call $SortableMenus() in the template.
        $record = Page::get()->filter(array('SortShowInSidebar' => 1))->first();
        $this->assertNotNull($record);

        // Test that it contains `SortableMenuFieldName` fiel
        $sortableMenus = $record->SortableMenu('ShowInSidebar');
        $this->assertEquals(
            'ShowInSidebar',
            $sortableMenus->SortableMenuFieldName,
            'SortableMenuFieldName should map to the DB field name (ie. ShowInSidebar) so that when you pass a menu down across templates, you\'re still able to cache it with a <% cache %> block'
        );

        // Test that it has expected menu count
        $this->assertEquals(3, $sortableMenus->count());

        // Make sure using $SortableMenuFieldName with cache blocks works as expected
        $expectedHTML = <<<HTML
<div class="TestPassInMenuList">
    <ul class="navigation">
        <li>
            <a href="/sidebar-menu-item-1/">
                Sidebar Menu Item #1
            </a>
        </li>
        <li>
            <a href="/sidebar-menu-item-2/">
                Sidebar Menu Item #2
            </a>
        </li>
        <li>
            <a href="/sidebar-menu-item-3/">
                Sidebar Menu Item #3
            </a>
        </li>
    </ul>
</div>
HTML;

        $actualHTML = $record->customise(array(
            'PassedInMenuList' => $sortableMenus,
        ))->renderWith(array('TestPassInMenuList'))->forTemplate();
        $this->assertEqualIgnoringWhitespace($expectedHTML, $actualHTML);
    }

    /**
     * Taken from "framework\tests\view\SSViewerTest.php"
     */
    private function assertEqualIgnoringWhitespace($a, $b, $message = '')
    {
        $this->assertEquals(preg_replace('/\s/', '', $a), preg_replace('/\s/', '', $b), $message);
    }
}
