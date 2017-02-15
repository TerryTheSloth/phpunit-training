#!/bin/bash
rm -rf ./coverage/  
phpunit --testdox  --bootstrap  src/Converter.php tests/ConverterTest --coverage-html ./coverage --whitelist ./src