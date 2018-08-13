<?php

namespace Symbiote\SortableMenu;

use Page;
use Symbiote\SortableMenu\SortableMenuExtensionException;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\DataList;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\CMS\Model\SiteTree;

class SortableMenuExtension extends DataExtension
{
    private static $menus = array();

    protected static $_cached_cache_max_lastedited = array();

    protected static $inConfigCall = false;

    public function __construct()
    {
        parent::__construct();
    }

    public static function get_extra_config($class, $extension, $args)
    {
        if (self::$inConfigCall) {
            throw new SortableMenuException(__FUNCTION__.': Recursion error.');
        }
        // NOTE(Jake): If 'get_extra_config' is really aiming to be deprecated
        //             post 3.2+, then this logic can moved to a CompositeDBField
        //             and the setup method can migrate to that.
        $menus = static::get_sortable_menu_configuration();
        $dbFields = array();
        foreach ($menus as $fieldName => $extraInfo) {
            $dbFields[$fieldName] = DBBoolean::class;
            $dbFields[$extraInfo['Sort']] = 'Int';
        }
        $result = array(
            'db' => $dbFields,
        );
        return $result;
    }

    /**
     * @return DataList
     */
    public function SortableMenu($fieldName)
    {
        return $this->SortableMenuUncached($fieldName);
    }

    /**
     * @return string
     */
    public function SortableMenuCacheKey($cacheKey)
    {
        if (!$cacheKey) {
            throw new SortableMenuException(__FUNCTION__.': Cannot have empty $cacheKey parameter.');
        }
        $baseClass = $this->getOwnerBaseclass();
        if (!isset(self::$_cached_cache_max_lastedited[$baseClass])) {
            // Reuse the max('LastEdited') / count() values for the class if
            // already queried. (Can shave off upto ~0.0010s per sortable menu)
            $list = DataList::create($baseClass);
            self::$_cached_cache_max_lastedited[$baseClass] = implode('_', array(
                $list->max('LastEdited'),
                (int)$list->count(),
            ));
        }
        return $cacheKey.'_'.self::$_cached_cache_max_lastedited[$baseClass];
    }

    /**
     * @return DataList
     */
    public function SortableMenuUncached($fieldName)
    {
        $menus = $this->getSortableMenuConfiguration();
        if (!isset($menus[$fieldName])) {
            throw new SortableMenuException(__FUNCTION__.': "'.$fieldName.'" hasn\'t been configured.');
        }
        $extraInfo = $menus[$fieldName];
        $class = $this->getOwnerBaseclass();
        $list = DataList::create($class)->filter(array(
            $fieldName => 1
        ))->sort($extraInfo['Sort']);
        // Store fieldname directly on list to use as a cache parameter
        $list->SortableMenuFieldName = $fieldName;
        return $list;
    }

    public function updateSettingsFields($fields)
    {
        $menus = Config::inst()->get(__CLASS__, 'menus');
        if (!$menus) {
            $menus = array();
        }
        foreach (Config::inst()->get(__CLASS__, 'menus') as $fieldName => $extraInfo) {
            $fieldTitle = $fieldName;
            if (isset($extraInfo['Title'])) {
                $fieldTitle = 'Show in "'.$extraInfo['Title'].'"?';
            }
            $fields->insertAfter(CheckboxField::create($fieldName, $fieldTitle), 'ShowInMenus');
        }
    }

    public function getSortableMenuConfiguration()
    {
        return static::get_sortable_menu_configuration();
    }

    public static function get_sortable_menu_configuration()
    {
        self::$inConfigCall = true;
        $menus = Config::inst()->get(__CLASS__, 'menus');
        self::$inConfigCall = false;
        if (!$menus) {
            $menus = array();
        }
        $result = array();
        foreach ($menus as $fieldName => $extraInfo) {
            if (!isset($extraInfo['Sort'])) {
                $extraInfo['Sort'] = 'Sort'.$fieldName;
            }
            if (!isset($extraInfo['Title'])) {
                $extraInfo['Title'] = $fieldName;
            }
            $result[$fieldName] = $extraInfo;
        }
        return $result;
    }

    /**
     * @return string
     */
    private function getOwnerBaseclass()
    {
        $owner = $this->getOwner();
        if ($owner instanceof Page) {
            return Page::class;
        }
        // Fallback to if somebody extended SiteTree
        if ($owner instanceof SiteTree) {
            return SiteTree::class;
        }
        $class = get_class($owner);
        $class = DataObject::getSchema()->baseDataClass($class);
        return $class;
    }
}
