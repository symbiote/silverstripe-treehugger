<?php

namespace Symbiote\SortableMenu\Tests;

use Page;
use SilverStripe\Core\Config\Config;
use Symbiote\SortableMenu\SortableMenuExtension;
use SilverStripe\Dev\FunctionalTest;

class SortableMenuDBTest extends FunctionalTest
{
    protected static $use_draft_site = true;

    protected $requireDefaultRecordsFrom = [
        Page::class,
    ];

    /*protected static $required_extensions = [
        Page::class => [
            SortableMenuExtension::class,
        ],
    ];*/

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
        parent::setUp();
        //
        foreach ($this->requireDefaultRecordsFrom as $className) {
            $instance = singleton($className);
            if (method_exists($instance, 'requireDefaultRecords')) {
                $instance->requireDefaultRecords();
            }
            if (method_exists($instance, 'augmentDefaultRecords')) {
                $instance->augmentDefaultRecords();
            }
        }
    }

    public function testDBFieldsApplyToDataObject()
    {
        $record = new Page();
        $record->Title = 'Footer Menu Item #1';
        $record->ShowInFooter = true;
        $record->write();

        // NOTE(Jake): 2018-08-09
        //
        // We re-retrieve the record. If it saved correctly
        // into the database, ShowInFooter will be `true`
        //
        $record = Page::get()->byID($record->ID);
        $this->assertEquals(
            true,
            $record->ShowInFooter,
            'ShowInFooter is not "true". This probably means that the \'menus\' Config/YML in the setUp() method isn\'t working as expected.'
        );

        $record = new Page();
        $record->Title = 'Sidebar Menu Item #1';
        $record->ShowInSidebar = true;
        $record->write();

        // NOTE(Jake): 2018-08-09
        //
        // We re-retrieve the record. If it saved correctly
        // into the database, ShowInSidebar will be `true`
        //
        $record = Page::get()->byID($record->ID);
        $this->assertEquals(
            true,
            $record->ShowInSidebar,
            'ShowInSidebar is not "true". This probably means that the \'menus\' Config/YML in the setUp() method isn\'t working as expected.'
        );
    }

    public function testRetrievingPagesByMenuDirectly()
    {
        // NOTE(Jake): 2018-08-09
        //
        // To ensure PostgreSQL support works SS 3.2 projects,
        // I need to pass in 0 or 1, not false or true.
        //
        // Otherwise I get the following error:
        // - pg_query_params(): Query failed: ERROR:  invalid input syntax for integer: ""
        //
        // I assume this is because PostgreSQL just casts the given value to a string and
        // in PHP when you cast `false` to a string, you get a blank string.
        //

        // Check we have no items in the menu
        $count = Page::get()->filter(array('ShowInFooter' => 0))->count();
        $this->assertEquals(0, $count);
        $count = Page::get()->filter(array('ShowInSidebar' => 0))->count();
        $this->assertEquals(0, $count);

        // Check we have 1 footer menu item
        $record = new Page();
        $record->Title = 'Footer Menu Item #1';
        $record->ShowInFooter = true;
        $record->write();
        $count = Page::get()->filter(array('ShowInFooter' => 1))->count();
        $this->assertEquals(1, $count);

        // Check we have 1 sidemenu menu item
        $record = new Page();
        $record->Title = 'Footer Menu Item #1';
        $record->ShowInSidebar = true;
        $record->write();
        $count = Page::get()->filter(array('ShowInSidebar' => 1))->count();
        $this->assertEquals(1, $count);
    }
}
