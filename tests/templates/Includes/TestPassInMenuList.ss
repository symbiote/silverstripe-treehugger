<div class="TestPassInMenuList">
    <% cached $SortableMenuCacheKey($PassedInMenuList.SortableMenuFieldName) %>
        <% include TestPassInMenuList_Menu MenuList=$PassedInMenuList %>
    <% end_cached %>
</div>
