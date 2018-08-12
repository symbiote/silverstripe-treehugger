<% if $MenuList %>
<ul class="navigation">
    <% loop $MenuList %>
        <li>
            <a href="$Link">
                $MenuTitle.XML
            </a>
        </li>
    <% end_loop %>
</ul>
<% end_if %>
