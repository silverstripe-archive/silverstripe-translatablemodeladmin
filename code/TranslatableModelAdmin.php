<?php

/**
 * Subclass of ModelAdmin with user-interface hooks for 
 */
abstract class TranslatableModelAdmin extends ModelAdmin {
	public static $record_controller_class = 'TranslatableModelAdmin_RecordController';
	public static $collection_controller_class = 'TranslatableModelAdmin_CollectionController';
	
	function init() {
		parent::init();
		Requirements::customScript("SiteTreeHandlers.controller_url = '" . $this->Link() . "';");
		Requirements::block(CMS_DIR . '/javascript/TranslationTab.js');
		Requirements::block(CMS_DIR . '/javascript/LangSelector.js');
		Requirements::javascript('translatablemodeladmin/javascript/TranslatableModelAdmin.js');

		// Set locale from the get param if one is given
		if(isset($_GET['locale'])) {
			Session::set('TranslatableModelAdmin.Locale', $_GET['locale']);
		}
		
		// We store the locale in the session because it was a less invasive change to ModelAdmin
		if($locale = Session::get('TranslatableModelAdmin.Locale')) {
			Translatable::set_current_locale($locale);
		}
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
	/**
	 * Create a new translation from an existing item, switch to this language and reload the tree.
	 */
	function createtranslation($data, $form, $edit) {
		$langCode = Convert::raw2sql($_REQUEST['newlang']);

		Translatable::set_current_locale($langCode);
		$translatedRecord = $this->currentRecord->createTranslation($langCode);
		
		$this->currentRecord = $translatedRecord;
		return $this->edit(null);
	}
}