<ol id="orderable-list-{$ID}" class="$extraClass orderable-list orderable-list--{$Options.Count} "<% if Description %> title="$Description"<% end_if %> data-orderable>
	<% loop $Options %>
		<li class="$Class orderable-listitem" data-id="$ID" tabindex="0">
			<span class="orderable-listitem-inner">
				<span class="orderable-listitem-handle"><span class="sr-only">drag</span></span>
				<span class="orderable-listitem-title">$Title $ID</span>
			</span>
		</li>
	<% end_loop %>
</ol>

<input type="hidden" name="$name" id="$ID" value="$value" class="orderable-list-value" />