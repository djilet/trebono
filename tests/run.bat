@ECHO OFF
cd ..
:start
	cls
	call vendor\bin\phpunit --debug --bootstrap vendor\autoload.php tests
	set /p choice="Press 'y' to restart or any other key to exit: "
	set choice=%choice:~0,1%
	if '%choice%'=='y' goto start