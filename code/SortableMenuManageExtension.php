<?php

class SortableMenuManageExtension extends Extension
{
    public function updateCMSFields(FieldList $fields)
    {
        // Determine whether the "Menus" tab has been created or not already.
        if ($this->owner->_sortable_menu_manage_getcmsfields_called) {
            return;
        }
        $this->owner->_sortable_menu_manage_getcmsfields_called = true;

        // Base class
        $basePageClass = '';
        if (SiteTree::has_extension('SortableMenu')) {
            // NOTE(Jake): 2018-08-09
            //
            // An SS 3.X project applied to the SiteTree so we need
            // this for backwards compatibility.
            //
            $basePageClass = 'SiteTree';
        } else if (Page::has_extension('SortableMenu')) {
            $basePageClass = 'Page';
        }

        // If one of the expected classes has the extension, then generate
        // the fields.
        if ($basePageClass !== '') {
            // Setup fields
            $rootTabSet = $fields->fieldByName('Root');
            $sortableMenuTab = $rootTabSet->fieldByName('SortableMenu');
            if (!$sortableMenuTab) {
                $sortableMenuTab = TabSet::create('SortableMenu', 'Menus');
                $rootTabSet->push($sortableMenuTab);
            }
            $sortableMenuTab->setTitle('Menus');
            if (!$sortableMenuTab instanceof TabSet) {
                $badClass = get_class($sortableMenuTab);
                throw new SortableMenuException('Sortable Menu must be a "TabSet", not "'.$badClass.'"');
            }
            $sortableMenuTab = $fields->findOrMakeTab('Root.SortableMenu', 'Menus');
            $menus = singleton('SortableMenu')->getSortableMenuConfiguration();
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
        if (!$class::has_extension('SortableMenu')) {
            throw new SortableMenuException($class.' does not have SortableMenu extension applied.');
        }
        $record = singleton($class);
        $list = $record->SortableMenu($fieldName);

        $gridField = GridField::create($fieldName, $fieldTitle, $list, $config = GridFieldConfig_RelationEditor::create());
        $gridField->setDescription('Any "Modified" or "Draft" pages must be saved and published after sorting to display on the live site.');

        $config->removeComponentsByType('GridFieldAddExistingAutocompleter');
        $config->removeComponentsByType('GridFieldAddNewButton');
        $config->removeComponentsByType('GridFieldEditButton');
        $config->removeComponentsByType('GridFieldDeleteAction');
        $config->removeComponentsByType('GridFieldFilterHeader');
        $config->addComponent(Injector::inst()->createWithArgs('GridFieldAddSortableMenuItem', array($fieldName)));
        if (class_exists('GridFieldOrderableRows')) {
            $config->addComponent(GridFieldOrderableRows::create($sortFieldName));
        }
        $dataColumns = $config->getComponentByType('GridFieldDataColumns');
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
                            singleton('CMSPageSettingsController')->Link('show'),
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
