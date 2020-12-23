TODO:

Hay funciones genericas.
Deberia meterlo en HomeController mas bien. O en un Controller parent de todo, no se si hay.

GIT project
---
note that main branch is `main`, not `master`, so `git pull/push origin main`  

ASSETS: scss, css and js  
---  
[...]

MOST USED FILES:  
===

frontend:
---
- `resources/app.js`  (npm watch will convert it into `/public/app.js`)  
- `*.vue`  The main component to show the buy orders.
-- It communicates with Laravel through axios in order to execute the actions.  
-- Laravel communicates with the vue component through app.js, using window.* variables and methods, and using `$emit`, in order to execute a fn inside vue to update the state of current symbol.  
-- `localStorage`, to save the status of current symbol and load it in case we reload page.

backend:
---
- `web.php` : for routing and endpoint of ajax calls.  
- `MyBinanceController` :  all important generic functions related to communic with binance  
- home.blade.php : Main view containing all of the rest. Only allowed if logged in.  
-- top row with balance and coins: `BinanceBalance.php` + `binance-balance.blade.php`  
-- left column with trades: `BinanceTrades.php` + `binance-trades.blade.php` 
--- single trade component: `SingleTrade.php`

DEVELOPMENT
--- 
npm install && npm run dev  
- edit .env  
source .env  
npm run watch  
cd public  
php -S localhost:9001
- open `localhost:9001` in browser.

HOW I CREATED THE APP 
---  
Valid for all Laravel 7 apps.
```
composer global require "laravel/installer=~1.1"  
composer create-project laravel/laravel lar-binance --prefer-dist  
- Create DB with mysql command  
- Edit .env with DB connection  
source .env  
php artisan config:cache && php artisan config:clear  
php artisan storage:link  
composer require laravel/ui  
chmod -R o+w storage  
php artisan migrate  
php artisan db:seed  
npm install && npm run dev  
```
- HOW I USE TWITTER BOOSTRAP:  
Edit app.scss  
```
@import 'variables';  
@import '~bootstrap/scss/bootstrap';  
```

RELOAD A PARTIAL COMPONENT in PHP (with axios ajax):  
===
- The partial Laravel component accepts params, <x-binance-trade symbol="ETCUSDT"/>  
- This is complex, I couldnt find a better way to do it.    
1) js call: `window.UIMethods.reloadTemplate('binance-trades', { symbol:symbol } )`  
2) `app.js`: calls with axios ajax the the endpoint called 
    '/load-partial-ajax?template=binance-trades&symbol=TFUELUSDT'  
3) `web.php`: routing of endpoint '/load-partial-ajax' to the generic view    `partial-load-template-ajax.blade`, pasing the $_GET as params to be used in the view.
4) `partial-load-template-ajax.blade` calls the right component depending on the template. In this case: <x-binance-trades/>
6) `BinanceTrades.php::__construct()` grabs the params from REQUEST, returns the view  
7) `views/components/binance-trades.blade.php` , shows all the info.  