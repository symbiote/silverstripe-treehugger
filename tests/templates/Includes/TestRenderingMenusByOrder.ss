<div class="footer">
    <% cached $SortableMenuCacheKey('ShowInFooter') %>
        <% if $SortableMenu('ShowInFooter') %>
            <ul class="footer-nav">
            <% loop $SortableMenu('ShowInFooter') %>
                <li>
                    <a href="$Link">
                        $MenuTitle.XML
                    </a>
                </li>
            <% end_loop %>
            </ul>
        <% end_if %>
    <% end_cached %>
</div>
