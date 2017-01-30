<?php

class SortableMenuSiteExtension extends Extension {
	public function updateSiteCMSFields(FieldList $fields) {
		$fields->addFieldToTab('Root', new TabSet('SortableMenu', 'Menus'));
		//$fields->addFieldToTab('Root.SortableMenu', $this->owner->createMenuGridField('SiteTree', 'ShowInMenus', 'Show in menus?', 'Sort'));
		$menus = singleton('SortableMenu')->getSortableMenuConfiguration();
		foreach ($menus as $fieldName => $extraInfo) {
			$fields->addFieldToTab('Root.SortableMenu', $this->owner->createMenuGridField('SiteTree', $fieldName, $extraInfo['Title'], $extraInfo['Sort']));
		}
	}

	public function createMenuGridField($class, $fieldName, $fieldTitle, $sortFieldName) {
		$record = singleton($class);
		$list = $record->SortableMenu($fieldName);

		$gridField = GridField::create($fieldName, $fieldTitle, $list, $config = GridFieldConfig_RelationEditor::create());
		$gridField->setDescription('Any "Modified" or "Draft" pages must be saved and published after sorting to display on the live site.');
		$tab = new Tab($fieldName, $fieldTitle);
		$tab->push($gridField);

		$config->removeComponentsByType('GridFieldAddExistingAutocompleter');
		$config->removeComponentsByType('GridFieldAddNewButton');
		$config->removeComponentsByType('GridFieldEditButton');
		$config->removeComponentsByType('GridFieldDeleteAction');
		$config->addComponent(new GridFieldAddSortableMenuItem($fieldName));
		$config->addComponent(GridFieldOrderableRows::create($sortFieldName));
		$dataColumns = $config->getComponentByType('GridFieldDataColumns');
		if ($dataColumns) {
			$dataColumns->setDisplayFields(array(
				'ID' => 'ID', 
				//'CMSPublishedState' => 'State',
				'getTreeTitle' => 'Title',
			));
			$dataColumns->setFieldFormatting(array(
                'getTreeTitle' => function($value, &$page) {
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
		return $tab;
	}
}