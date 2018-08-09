# Quick Start

1) Install via Composer as per README.md

2) Configure additional menus in YML like so, and dev/build?flush=all

```yml
---
Name: menus
After:
  - 'framework/*', 'cms/*'
---
SortableMenu:
  menus:
    ShowInFooter:
      Title: 'Footer'
    ShowInSidebar:
      Title: 'Sidebar'
Page:
  extensions:
    - SortableMenu
```

3) To use this in Page templates, use the following:

```html
<% cached $SortableMenuCacheKey('ShowInFooter') %>
    <% if $SortableMenu('ShowInFooter') %>
        <ul>
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
```

# Showing menus on non-Page contexts

If you need to use this in other contexts, like a plain Controller, you will need to wrap the prefix the $SortableMenu calls.

**SiteConfig**
```html
<% cached $SiteConfig.SortableMenuCacheKey('ShowInFooter') %>
    <% if $SiteConfig.SortableMenu('ShowInFooter') %>
        <ul>
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
```

**For Multisites**
```html
<% cached $Site.SortableMenuCacheKey('ShowInFooter') %>
    <% if $Site.SortableMenu('ShowInFooter') %>
        <ul>
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
```
