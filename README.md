# Buto-Plugin-NewsSitenews
- Show list of news where to click on one to read more.
- Admin buttons are included if user has role webadmin.
- Using MySQL.


## Schema
```
/plugin/news/sitenews/mysql/schema.yml
```


## Settings
```
plugin_modules:
  sitenews:
    plugin: 'news/sitenews'
```
```
plugin:
  news:
    sitenews:
      enabled: true
      data:
        mysql: 'yml:/../buto_data/demokrationline/mysql.yml'
```

## Widget
List news.
```
type: widget
data:
  plugin: 'news/sitenews'
  method: flash
  data:
```

Default headline is News (optional).
```
    headline: Press release
```
Show all news in database. Default shows one news and a button to show all (optional).
```
    show_all: true
```
