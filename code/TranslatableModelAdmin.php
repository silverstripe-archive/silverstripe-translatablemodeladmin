<?php

/**
 * Subclass of ModelAdmin with user-interface hooks for {@link Translatable} functionality.
 */
abstract class TranslatableModelAdmin extends ModelAdmin {
	public static $record_controller_class = 'TranslatableModelAdmin_RecordController';
	public static $collection_controller_class = 'TranslatableModelAdmin_CollectionController';
	
	/**
	 * @var String $Locale
	 */
	public $Locale = null;
	
	function init() {
		parent::init();
		Requirements::customScript("SiteTreeHandlers.controller_url = '" . $this->Link() . "';");
		Requirements::block(CMS_DIR . '/javascript/TranslationTab.js');
		Requirements::block(CMS_DIR . '/javascript/LangSelector.js');
		Requirements::javascript('translatablemodeladmin/javascript/TranslatableModelAdmin.js');
		
		// Similar to CMSMain->init()
		if($this->getRequest()->requestVar("Locale")) {
			$this->Locale = $this->getRequest()->requestVar("Locale");
		} elseif($this->getRequest()->requestVar("locale")) {
			$this->Locale = $this->getRequest()->requestVar("locale");
		} else {
			$this->Locale = Translatable::default_locale();
		}
		Translatable::set_current_locale($this->Locale);
	}

	/**
	 * Returns managed models' create, search, and import forms.
	 * 
	 * Note from Sam at SilverStripe: This change, adding the actual collection controllers to the 
	 * result instead of ArrayData objects, should be propagated to core at some stage.
	 */
	protected function getModelForms() {
		$modelClasses = $this->getManagedModels();
		
		$forms = new DataObjectSet();
		foreach($modelClasses as $modelClass) {
			$this->$modelClass()->SearchForm();

			$forms->push($this->$modelClass());
		}
		
		return $forms;
	}
}

class TranslatableModelAdmin_CollectionController extends ModelAdmin_CollectionController {
	/**
	 * Return the ClassName for <% control ModelForms %> in the parent ModelAdmin
	 */
	function ClassName() {
		return $this->modelClass;
	}
	
	/**
	 * Return the Title for <% control ModelForms %> in the parent ModelAdmin
	 */
	function Title() {
		return singleton($this->modelClass)->singular_name();
	}
	
	function CreateForm() {
		$form = parent::CreateForm();
		$form->Fields()->push(new HiddenField('Locale', null, $this->parentController->Locale));
		
		return $form;
	}
	
	function AddForm() {
		$form = parent::AddForm();
		$form->Fields()->push(new HiddenField('Locale', null, $this->parentController->Locale));
		
		return $form;
	}
	
	function SearchForm() {
		$form = parent::SearchForm();
		$form->Fields()->push(new HiddenField('Locale', null, $this->parentController->Locale));
		
		return $form;
	}
	
	function getSearchQuery($searchCriteria) {
		$context = singleton($this->modelClass)->getDefaultSearchContext();
		$context->addFilter(new ExactMatchFilter('Locale', $this->parentController->Locale));
		return $context->getQuery($searchCriteria);
	}

	/**
     * Returns all languages with languages already used appearing first.
     * Called by the SSViewer when rendering the template.
     */
    function LangSelector() {
		if(Object::has_extension($this->modelClass, 'Translatable')) {
			$member = Member::currentUser(); //check to see if the current user can switch langs or not
			if(Permission::checkMember($member, 'VIEW_LANGS')) {
				$dropdown = new LanguageDropdownField(
					'LangSelector', 
					'Language', 
					array(), 
					$this->modelClass, 
					'Locale-English'
				);
				$dropdown->setValue(Translatable::get_current_locale());
				return $dropdown;
	        }
        
	        //user doesn't have permission to switch langs so just show a string displaying current language
	        return i18n::get_locale_name( Translatable::get_current_locale() );
	}
    }
}

class TranslatableModelAdmin_RecordController extends ModelAdmin_RecordController {
	
	function EditForm() {
		$form = parent::EditForm();
		
		if($this->currentRecord->hasExtension('Translatable')) {
			// TODO Exclude languages which are already translated into 
			$dropdown = new LanguageDropdownField(
				'NewTransLang', 
				_t('TranslatableModelAdmin.LANGDROPDOWNLABEL', 'Language'), 
				array(), 
				$this->currentRecord->class, 
				'Locale-English'
			);
			$action = new InlineFormAction(
				'createtranslation', 
				_t('TranslatableModelAdmin.CREATETRANSBUTTON', 
				"Create translation")
			);
			$header = new HeaderField(
				'ExistingTransHeader', 
				_t('TranslatableModelAdmin.EXISTINGTRANSTABLE', 'Existing Translations'),
				4
			);
			// TODO Exclude the current language
			$table = new TableListField(
				'Translations',
				$this->currentRecord->class
			);
			$table->setPermissions(array('show'));
			$table->setCustomSourceItems($this->currentRecord->getTranslations());
			$action->includeDefaultJS = false;
			if($form->Fields()->hasTabSet()) {
				$form->Fields()->findOrMakeTab(
					'Root.Translations', 
					_t("TranslatableModelAdmin.TRANSLATIONSTAB", "Translations")
				);
				$form->Fields()->addFieldToTab('Root.Translations', $header);
				$form->Fields()->addFieldToTab('Root.Translations', $table);
				$form->Fields()->addFieldToTab('Root.Translations', $dropdown);
				$form->Fields()->addFieldToTab('Root.Translations', $action);
			} else {
				$form->Fields()->push(new HeaderField(
					'TranslationsHeader',
					_t("TranslatableModelAdmin.TRANSLATIONSTAB", "Translations")
				));
				$form->Fields()->push($header);
				$form->Fields()->push($table);
				$form->Fields()->push($dropdown);
				$form->Fields()->push($action);
			}
			// TODO This is hacky, but necessary to get proper identifiers
			$form->Fields()->setForm($form);
			
		}
		
		return $form;
	}
	
	/**
	 * Create a new translation from an existing item, switch to this language and reload the tree.
	 */
	function createtranslation($data, $form, $edit) {
		$langCode = Convert::raw2sql($_REQUEST['newlang']);

		Translatable::set_current_locale($langCode);
		$translatedRecord = $this->currentRecord->createTranslation($langCode);

		$this->currentRecord = $translatedRecord;
		
		// TODO Return current language as GET parameter
		return $this->edit(null);
	}
}