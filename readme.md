COMBINATOR
=====================


[![COMBINATOR LOGO](https://raw.githubusercontent.com/phphleb/combinator/0de1d4cc1243cd623f843f01073cf010453b3f1b/logo.png)](https://github.com/phphleb/combinator/tree/master)


[![HLEB2](https://img.shields.io/badge/HLEB-2-darkcyan)](https://github.com/phphleb/hleb) ![PHP](https://img.shields.io/badge/PHP-^8.2-blue) [![License: MIT](https://img.shields.io/badge/License-MIT%20(Free)-brightgreen.svg)](https://github.com/phphleb/hleb/blob/master/LICENSE)



**Сборщик компонентов для библиотек фреймворка [HLEB2](https://github.com/phphleb/hleb)**

При наличии установленных библиотек (компонентов), которые внедряются в проект или удаляются с помощью
библиотеки [phphleb/updater](https://github.com/phphleb/updater), можно автоматизировать такие процессы, запуская их выполнение поочерёдно. 

Образец оформления вы можете увидеть в библиотеке [phphleb/demo-updater](https://github.com/phphleb/demo-updater).

Установка при помощи Composer:
```bash
composer require phphleb/combinator
```
_____

### Стандартное обновление компонентов

Установка/обновление компонентов:

```bash
php console phphleb/combinator add
```

Удаление компонентов из проекта:

```bash
php console phphleb/combinator remove
```

_________________________

### Автоматическое обновление компонентов

Для автоматического цикла действий с компонентами, нужно создать конфигурационный файл
в одной из папок проекта, рекомендуется в папке /config/, по образцу файла 'updater.json'.
В этой конфигурации будут присутствовать установочные дополнения к конфигурационным файлам библиотек.

Добавлением параметра _--config-path=_ к команде назначается файл конфигурации согласно пути из корневой папки проекта.


```bash
php console phphleb/combinator add --config-path=/config/combinator.json
```

Также можно отменить вывод команды в консоль (--quiet) или отключить только интерактивный режим (--no-interaction).

При помощи автоматических действий компоненты разбиваются на наборы, описанные в конфигурационных файлах. Также это будет полезным при 
обновлении библиотек пользователями проекта, чтобы он каждый раз не указывал соответствие папок в установщике, а один раз изменил в
конфигурационном файле.
