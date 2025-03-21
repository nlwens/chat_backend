# Chat Application Backend

## Introduction
This document provides an overview of the **Chat Application** Backend project, which is a backend implementation for a simple chat application written in **PHP**. The application allows users to create public chat groups, join these groups, and send messages within them. The backend is built using the **Slim Framework** and stores data in an **SQLite** database. Communication between the client and server is handled via a RESTful JSON API.

---

## Features
- **Group Management**: Users can create and join public chat groups. 
- **Message Handling**: Users can send messages to groups and retrieve all messages within a group. 
- **User Identification**: Users are identified by a unique token or ID, which is included in API requests. 
- **Simple API**: The backend provides a clean and easy-to-use API for client interactions.

---

## Develop environment
This project was developed and tested on **Ubuntu (version 22.04 LTS)**. All command-line operations, including database setup, dependency installation, and running the server, were performed in an Ubuntu terminal. Below are the key tools and versions used:
- PHP 8
- Slim Framework 4
- SQLite

If you are using a different operating system, the commands may need to be adjusted accordingly. However, the provided instructions should work seamlessly on any Ubuntu-based environment.

---

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
   
---

## Technical Docs
- [API Documentation](src/README.md)
- [Database Documentation](database/README.md)
- [Test Documentation](tests/README.md)

---

## Future Improvements
- Support user authentication, preferable JWT token.
- Add full CRUD for all 3 sources.
- Optimize database queries for better performance.