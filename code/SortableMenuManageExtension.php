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
            $menuTab->push($this->owner->createMenuGridField('SiteTree', $fieldName, $fieldTitle, $extraInfo['Sort']));
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
        $config->addComponent(GridFieldOrderableRows::create($sortFieldName));
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
