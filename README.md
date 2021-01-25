# Kata-Bank-Ocr
Php solution for this problem: https://codingdojo.org/kata/BankOCR/

## Important!

This is Symfony console application and requires Docker, to run it. 

If you don't have Docker, you can install it with this tutorial: https://docs.docker.com/engine/install/ubuntu/

## Install application

Clone this repository.

If you have already Docker and docker-compose, just type in console:

```bash
docker-compose run --rm composer install
```

## Run scanning

There are few example test files in data catalog. Choose one of them and use as input file:

```bash
docker-compose run --rm console app:scan-segment-accounts data/test.txt
```

Now you should see something like this:

```bash
Creating kata2_console_run ... done
=================================================================
    _  _     _  _  _  _  _
  | _| _||_||_ |_   ||_||_|
  ||_  _|  | _||_|  ||_| _|

123456789

=================================================================
```
