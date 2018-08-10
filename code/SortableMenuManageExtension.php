<?php

namespace SilbinaryWolf\SortableMenu;

use Page;
use SilverStripe\Forms\FieldList;
use Symbiote\SortableMenu\SortableMenu;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Core\Injector\Injector;
use SilbinaryWolf\SortableMenu\GridFieldAddSortableMenuItem;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\CMS\Controllers\CMSPageSettingsController;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Extension;



class SortableMenuManageExtension extends Extension
{
    public function updateCMSFields(FieldList $fields)
    {
        // Base class
        $basePageClass = '';
        if (SiteTree::has_extension(SortableMenu::class)) {
            // NOTE(Jake): 2018-08-09
            //
            // An SS 3.X project applied to the SiteTree so we need
            // this for backwards compatibility.
            //
            $basePageClass = SiteTree::class;
        } elseif (Page::has_extension(SortableMenu::class)) {
            $basePageClass = 'Page';
        }

        // If one of the expected classes has the extension, then generate
        // the fields.
        if ($basePageClass !== '') {
            // Setup fields
            $rootTabSet = $fields->fieldByName('Root');
            $sortableMenuTab = $rootTabSet->fieldByName(SortableMenu::class);
            if (!$sortableMenuTab) {
                $sortableMenuTab = TabSet::create(SortableMenu::class, 'Menus');
                $rootTabSet->push($sortableMenuTab);
            }
            $sortableMenuTab->setTitle('Menus');
            if (!$sortableMenuTab instanceof TabSet) {
                throw new SortableMenuException('Sortable Menu must be a "TabSet", not "'.get_class($sortableMenuTab).'"');
            }
            $sortableMenuTab = $fields->findOrMakeTab('Root.SortableMenu', 'Menus');
            $menus = singleton(SortableMenu::class)->getSortableMenuConfiguration();
            foreach ($menus as $fieldName => $extraInfo) {
                $fieldTitle = $extraInfo['Title'];
                // NOTE(Jake): Check if Tab has already been created by user-code / outside of the module
                $menuTab = $sortableMenuTab->fieldByName($fieldName);
                if (!($menuTab instanceof Tab)) {
                    $menuTab = Tab::create($fieldName, $fieldTitle);
                    $sortableMenuTab->push($menuTab);
                }
                $menuTab->setTitle($fieldTitle);
                $menuTab->push($this->owner->createMenuGridField($basePageClass, $fieldName, $fieldTitle, $extraInfo['Sort']));
            }
        }
    }

    /**
     * Support 'Site' from Multisites module
     */
    public function updateSiteCMSFields(FieldList $fields)
    {
        $this->updateCMSFields($fields);
    }

    public function createMenuGridField($class, $fieldName, $fieldTitle, $sortFieldName)
    {
        if (!class_exists($class)) {
            throw new SortableMenuException($class.' does not exist.');
        }
        if (!$class::has_extension(SortableMenu::class)) {
            throw new SortableMenuException($class.' does not have SortableMenu extension applied.');
        }
        $record = singleton($class);
        $list = $record->SortableMenu($fieldName);

        $gridField = GridField::create($fieldName, $fieldTitle, $list, $config = GridFieldConfig_RelationEditor::create());
        $gridField->setDescription('Any "Modified" or "Draft" pages must be saved and published after sorting to display on the live site.');

        $config->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
        $config->removeComponentsByType(GridFieldAddNewButton::class);
        $config->removeComponentsByType(GridFieldEditButton::class);
        $config->removeComponentsByType(GridFieldDeleteAction::class);
        $config->removeComponentsByType(GridFieldFilterHeader::class);
        $config->addComponent(Injector::inst()->createWithArgs(GridFieldAddSortableMenuItem::class, array($fieldName)));
        if (class_exists(GridFieldOrderableRows::class)) {
            $config->addComponent(GridFieldOrderableRows::create($sortFieldName));
        }
        $dataColumns = $config->getComponentByType(GridFieldDataColumns::class);
        if ($dataColumns) {
            $dataColumns->setDisplayFields(array(
                'ID' => 'ID',
                'getTreeTitle' => 'Title',
            ));
            $dataColumns->setFieldFormatting(array(
                'getTreeTitle' => function ($value, &$page) {
                    return sprintf(
                        '<a class="action-detail" href="%s">%s</a>',
                        Controller::join_links(
                            // CMSPageEditController - for regular fields
                            singleton(CMSPageSettingsController::class)->Link('show'),
                            (int)$page->ID
                        ),
                        $page->TreeTitle // returns HTML, does its own escaping
                    );
                }
            ));
        }
        return $gridField;
    }
}
