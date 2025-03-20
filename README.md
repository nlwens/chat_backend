# Chat Application Backend

This is a backend for a chat application written in PHP using the Slim framework. It allows users to create groups, join groups, and send messages within groups.

## Features
- Create public chat groups.
- User join chat groups
- Send messages to groups.
- List all messages in a group.
- RESTful JSON API for communication.

## Technologies
- PHP 8.x
- Slim Framework
- SQLite
- Ubuntu

## Installation
1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/chat-backend.git

2. Install dependencies:
   ```bash
   composer install

3. Initialize the database:
   ```bash
   sqlite3 database/chat.db < database/schema.sql
   
4. Insert dummy data (optional, but must initiate the database first)
   ```bash
   sqlite3 database/chat.db < database/dummy_data.sql
   
5. Start the server
    ```bash
   php -S localhost:8080 -t public

## Tests

1. Run all tests with phpunit
   ```bash
   vendor/bin/phpunit

## Future Improvements
- Support user authentication, preferable JWT token.
- Add full CRUD for all 3 sources.
- Optimize database queries for better performance.