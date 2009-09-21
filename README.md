TranslatableModelAdmin Module
=============================

This module provides additional developer tools for creating ModelAdmin interfaces on Translatable
data objects.  It doesn't provide any new functionality out of the box; you will need to write code
against the new APIs in order to use it.

Maintainer Contact
------------------

 * Sam Minnee (sminnee, sam (at) silverstripe (dot) com)

Requirements
------------

 * SilverStripe 2.3 or newer

Installation
------------

To install, simply unpack the module into a translatablemodeladmin directory within your project.

Usage
-----

Create a ModelAdmin interface for your application, as outlined in 
[the ModelAdmin documentation](http://doc.silverstripe.com/doku.php?id=modeladmin).

However, instead of using ModelAdmin as the base class:

	class MyAdmin extends ModelAdmin {
		...
	}

Use TranslatableModelAdmin:

	class MyAdmin extends TranslatableModelAdmin {
		...
	}

Your ModelAdmin will provide a language dropdown at the top of the left-hand panel for DataObjects
that include the Translatable extension.

	class MyData extends DataObject {
		static $extensions = array(
			'Translatable'
		);
		
		...
	}
	
You are also allowed to build a TranslatableModelAdmin that manages multiple classes, some of which
have the Translatable extension and some of which do not.