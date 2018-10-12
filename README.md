# Buto-Plugin-NewsSitenews
Plugin to show news from MySql database.

Backend to edit news and a widget to flash them.


Schema: /plugin/news/sitenews/mysql/schema.yml

Admin settings.
```
plugin_modules:
  sitenews:
    plugin: 'news/sitenews'
```

Widget enable and mysql settings.
```
plugin:
  news:
    sitenews:
      enabled: true
      data:
        mysql: 'yml:/../buto_data/demokrationline/mysql.yml'
```

Widget for flash news and also edit by user who has role webadmin.
```
type: widget
data:
  plugin: 'news/sitenews'
  method: flash
  data:
    style: '.news-sitenews-item {height:300px;overflow:auto}'
```
