## Compare - компонент для сравнения товаров для MODX Revolution 2.x + miniShop2

Этот компонент позволяет добавить на ваш сайт (созданный на базе MODX Revolution + miniShop2)
механизм для сравнения товаров по характеристикам.

### Сборка пакета и установка

**Из исходного кода:**

Для сборки пакета скачайте исходный код в каталог ```Extras/compare/```
в корне вашего сайта и запустите скрипт ```Extras/compare/_build/build.php```, 
компонент будет собран и установлен автоматически.

**Готовый пакет**

Вы можете скачать готовый пакет в разделе [Releases](https://github.com/createit-ru/modx-compare/releases) 
и установить его через Менеджер пакетов вашего сайта.

### Интеграция на сайт

**Шаг 1.** Добавьте на страницы сайта скрипты и стили компонента:
```html
<!-- css, добавляем в <head> -->
<link rel="stylesheet" href="/assets/components/compare/css/compare.css">

<!-- js, в низ страницы, перед закрывающимся </body> -->
<script src="/assets/components/compare/js/compare.js" defer></script>
```

**Шаг 2.** Добавьте у товара кнопку для добавления в сравнение.

Пример #1, просто иконка:
```html
<div class="compare compare--load" compare-root data-id="[[*id]]" data-list="default">
    <button class="compare__button" aria-label="Добавить в сравнение">
        <svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect y="8" width="3" height="10" fill="#A6A6A6"/>
            <rect x="7" width="3" height="18" fill="#A6A6A6"/>
            <rect x="14" y="4" width="3" height="14" fill="#A6A6A6"/>
        </svg>
    </button>
</div>
```
Пример #2, с текстом:
```html
<div class="compare compare--load" compare-root data-id="[[*id]]" data-list="default">
    <button class="compare__button" type="button" aria-label="Добавить в сравнение">
        <svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect y="8" width="2" height="10" fill="#125190"></rect>
            <rect x="7" width="2" height="18" fill="#125190"></rect>
            <rect x="14" y="4" width="2" height="14" fill="#125190"></rect>
        </svg>
        <span class="compare__button-text" data-add="В сравнение" data-remove="В сравнении">В сравнение</span>
    </button>
    <a href="/compare?list=default" class="compare__go">Сравнить&nbsp;(<span class="compare__count"></span>)</a>
</div>
```

Обратите внимание на классы и аттрибуты, начинающиеся с ```compare```, они обязательны для корректной работы компонента.

**Шаг 3.** Создайте страницу ```/compare```, где будет вызов сниппета:
```
[[!compare?
    &fields=`price,vendor,size,color,weight,made_in,option_1,option_2`
    &best=`price:min`
    &list=`default`
    &tpl=`compare.Page`
]]
```

Описание параметров:
* ```fields``` - список полей товара и опций (option_1, ...) для сравнения.
* ```best``` - список полей, для которых подсветить лучшие (min или max) значения (только числовые), например, минимальная цена.
* ```list``` - список сравнения, по-умолчанию ```default```. Используйте разные списки, чтобы не сравнивать холодильники с телефонами.
* ```tpl``` - шаблон с html разметкой.

**Шаг 4.** Добавьте в шапку сайта ссылку на страницу сравнения (по аналогии с мини-корзиной miniShop2):

```html
<a href="/compare" class="compare-mini">
    Сравнить <i class="compare-mini__count"></i>
</a>
```
Здесь будет отображаться кол-во товаров в сравнении.

Когда в сравнении будут товары, то к ссылке добавится класс ```compare-mini--active```, что позволит управлять её внешним видом (или видимостью).

### Заключение

Обсуждение компонента, вопросы автору: https://modx.pro/components/25312