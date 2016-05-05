# Expression
Expressions
The application's purpose is to evaluate a string that contains a boolean expression, and return the expression result.

It receives an argument of string type (Ex: T|F&T&(F|T) ), evaluates this expression and returns T (true) or F (False).

The expression may only contain accepted characters ( T,F,&,|,(,) ) and it must be a valid boolean expression otherwise the application will return an error reponse.

Instalation:
Pre-requisites:
	-apache2
	-php5
	-composer
	-symfony component of composer

Download source code from github repository(git@github.com:lauram88/expression.git) and copy it into var/www/html/path/where/to/install" directory.

This application uses Composer and its Symfony component.
Composer installation is necessary and be installed in your path/expression;Also to Composer we need to add its component Symfony;

Composer install : curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/youtdirectory/expression --filename=composer
Add Symfony component: php composer.phar require symfony/finder.

After starting the Apache server, the application may be accessed at "http://localhost/expression..." from a browser.


Examples:
Expression       	Result
   F|T 	    	-       T
   T&T		-	T
   T&F		-	F
   T&(F|T)  	- 	T
F|(F&T)		-	F
F|F&T|F&F	-	F
T&(F|T)|F&(T&F)	-	F		
T&(T|(T&F)&T)	-	T
T&&(T||T)	-	Error message
T&(T|T		-	Error message
