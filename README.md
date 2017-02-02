# Introduction

Add additional menus (such as footer, sidebars) programmatically.

This will automatically add a checkbox in Settings for each page type as well as give you a GridField
to manage all the menus on SiteConfig. ("Site" page type if you're using Multisites module)

# Composer Install

```
composer require silbinarywolf/silverstripe-treehugger:~1.1.0
```

# Quick Start

1) Drop folder in, dev/build?flush=all

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

3) Add into your template:
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

# Supports
- Silverstripe 3.2 and higher
- Multisites
