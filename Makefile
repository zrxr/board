include .env
export $(shell sed 's/=.*//' .env)


install:
	cd src/webroot
	wget 'https://code.jquery.com/jquery-3.6.0.min.js' -O 'src/webroot/jquery.js'

up:
	docker-compose up
