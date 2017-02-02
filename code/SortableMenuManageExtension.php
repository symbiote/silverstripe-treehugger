<?php

class SortableMenuManageExtension extends Extension {
	/**
	 * Determine whether the Menus tab has been created or not already.
	 *
	 * @var boolean
	 */
	protected $fields_added = false;

	public function updateCMSFields(FieldList $fields) {
		if ($this->fields_added) {
			return;
		}
		$this->fields_added = true;

		$fields->findOrMakeTab('Root.SortableMenu', 'Menus');
		$menus = singleton('SortableMenu')->getSortableMenuConfiguration();
		foreach ($menus as $fieldName => $extraInfo) {
			$fieldTitle = $extraInfo['Title'];
			$fields->findOrMakeTab('Root.SortableMenu.'.$fieldName, $fieldTitle);
			$fields->addFieldToTab('Root.SortableMenu.'.$fieldName, $this->owner->createMenuGridField('SiteTree', $fieldName, $fieldTitle, $extraInfo['Sort']));
		}
	}

	/**
	 * Support 'Site' from Multisites module
	 */
	public function updateSiteCMSFields(FieldList $fields) {
		$this->updateCMSFields($fields);
	}

	public function createMenuGridField($class, $fieldName, $fieldTitle, $sortFieldName) {
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
		return $gridField;
	}
}