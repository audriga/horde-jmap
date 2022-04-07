.PHONY: build
build:
	composer update --prefer-dist --no-dev
	composer archive -f zip --dir=build/

.PHONY: clean
clean:
	rm -rf build
