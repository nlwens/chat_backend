# Test
## Introduction
This document provides a comprehensive guide to testing the backend of the chat application. The tests include both **unit tests** (written with PHPUnit) and **HTTP API tests** (using .http files for manual testing).


The goal of this testing suite is to:

- **Validate core functionality**: Ensure that all features (e.g., creating groups, sending messages, joining/leaving groups, user identification) work as expected.

- **Handle edge cases**: Test invalid tokens, missing parameters, and other error conditions.

- **Ensure security**: Verify that authentication and authorization mechanisms are correctly implemented.

---
## Knowing Before Running
- When run http api test, it will influence the real data in database. It is recommended to backup the database or use docker container to prevent real data being polluted.
- All unit tests use an in-memory SQLite database (sqlite::memory:), it will not influence the real database.
- Mocked dependencies are used for middleware request handling.

---
## Running Tests
### Unit Test
To run all **unit tests** please make sure you have installed phpunit. Then run the bash command below.
   ```bash
      vendor/bin/phpunit
   ```
### HTTP API tests 
1. Run the server on http://localhost:8080, it can be done by using bash command below. If your server is running on a different port, you can update the port value in the http_api/http-client.env.json file to match your actual server port.
     ```bash
     php -S localhost:8080 -t public
     ```
2. 
   - For **PHPStorm** user, please open **http_test/test.http** and run the tests in **"dev"** environment.
   - For **VSCode** user, please install and run the tests by **REST Client extension**
