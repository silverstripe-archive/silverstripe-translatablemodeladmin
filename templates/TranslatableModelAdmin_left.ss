
<% require javascript(sapphire/thirdparty/tabstrip/tabstrip.js) %>
<% require css(sapphire/thirdparty/tabstrip/tabstrip.css) %>
<div id="LeftPane">

	<!-- <h2><% _t('SEARCHLISTINGS','Search Listings') %></h2> -->
	<div id="SearchForm_holder" class="leftbottom">		
	    <% if SearchClassSelector = tabs %>
		<ul class="tabstrip">
		<% control ModelForms %>
			<li class="$FirstLast"><a href="#{$Form.Name}_$ClassName">$Title</a></li>
		<% end_control %>
		</ul>
		<% end_if %>
		
		<% if SearchClassSelector = dropdown %>
		<p id="ModelClassSelector">
		    Search for:
    		<select>
            	<% control ModelForms %>
            		<option value="{$Form.Name}_$ClassName">$Title</option>
            	<% end_control %>
    		</select>
    	</p>
    	<% end_if %>
		
		<% control ModelForms %>
		<div class="tab" id="{$Form.Name}_$ClassName">
			<% if LangSelector %>
			<div id="LangSelector_holder">
				Language: $LangSelector
			</div>
			<% end_if %>

			<% if CreateForm %>
				<h3><% _t('ADDLISTING','Add') %></h3>
				$CreateForm
			<% end_if %>
			
			<h3><% _t('SEARCHLISTINGS','Search') %></h3>
			$SearchForm
			
			<% if ImportForm %>
				<h3><% _t('IMPORT_TAB_HEADER', 'Import') %></h3>
				$ImportForm
			<% end_if %>
			
		</div>
		<% end_control %>
	</div>
</div>