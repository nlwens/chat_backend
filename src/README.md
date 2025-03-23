# **API Specification**

This document provides detailed information about the RESTful JSON API for the Chat Application Backend.

**Base URL**: `http://localhost:8080`

---

## Authentication

No authentication is required for this API. Users are identified by a unique token or ID included in the request header.

---

## **1. Users**

### **POST /users**

- **Description**: Create a new user to the database.
- **Request Parameters**: None
- **Request**:

```json
{
  "username": "user1"
}
```

- **Response**:

```json
{
  "id": 1,
  "username": "user1",
  "token": "token1"
}
```

> **Note**: According to the assessment requirements, there is no need to implement CRUD operations for users as the
> users
> don't need to login, register, logout or change information. Therefore, only a POST API is designed for user creation
> to
> facilitate testing purposes.

## **2. Groups API**

### **GET /groups**

- **Description**: Retrieve a list of all groups, with details such as group id, group name, and created time.
- **Request Parameters**: None
- **Header**:

```http
Authorization: Bearer <your_token>
X-User-Id: <your_id>
```

- **Response**:

```json
[
  {
    "id": 1,
    "name": "Group A",
    "created_at": "2023-10-01T12:00:00Z"
  },
  {
    "id": 2,
    "name": "Group B",
    "created_at": "2023-10-01T12:05:00Z"
  }
]
```
#### **Error Responses**
- **401 Unauthorized**:

    ```json
    {
      "error": "Missing or invalid token"
    }
    ```
    - **Occurs When**: When the user doesn't have valid token, checked by AuthMiddleware.

---

### **POST /groups**

- **Description**: Create a new group to the database.
- **Request Parameters**: None
- **Header**:

```http
Authorization: Bearer <your_token>
X-User-Id: <your_id>
```

- **Request**:

```json
{
  "group_name": "Group C"
}
```

- **Response**:

```json
{
  "group_id": 3
}
```
#### **Error Responses**
- **401 Unauthorized**:

    ```json
    {
      "error": "Missing or invalid token"
    }
    ```

    - **Occurs When**: When the user doesn't have valid token, checked by AuthMiddleware.

> **Note**: The user will join the group that he/she created automatically.

---

### **POST /groups/:groupId/members**

- **Description**: Allow user to join a group based on group id.
- **Request Parameters**: None
- **Header**:

```http
Authorization: Bearer <your_token>
X-User-Id: <your_id>
```

- **Response**:

```json
{
  "message": "User successfully joined the group"
}
```

#### **Error Responses**
- **401 Unauthorized**:

    ```json
    {
      "error": "Missing or invalid token"
    }
    ```

    - **Occurs When**: When the user doesn't have valid token, checked by AuthMiddleware.

- **400 Bad Request**:

    ```json
    {
      "error": "User ID and Group ID are required"
    }
    ```

    - **Occurs When**: When the user ID (from header) or group ID (from argument) is/are not provided.

- **404 Not Found**:

    ```json
    {
      "error": "Cannot find the group"
    }
    ```

    - **Occurs When**: The user try to join a group that cannot be found in database.

- **409 Conflict**:

    ```json
    {
      "error": "User is already in the group"
    }
    ```

    - **Occurs When**: The user try to join a group that he/she is already in.


- **500 Internal Server Error**:

    ```json
    {
      "error": "Unknown error occurred"
    }
    ```
---

### **DELETE /groups/:groupId/members**

- **Description**: Allow user to leave a group based on group id.
- **Request Parameters**: None
- **Header**:

```http
Authorization: Bearer <your_token>
X-User-Id: <your_id>
```

- **Response**:

```json
{
  "message": "User successfully left the group"
}
```

#### **Error Responses**

- **400 Bad Request**:

    ```json
    {
      "error": "User ID and Group ID are required"
    }
    ```

    - **Occurs When**: When the user ID (from header) or group ID (from argument) is/are not provided.


- **403 Forbidden**:

    ```json
    {
      "error": "User is not in the group"
    }
    ```

    - **Occurs When**: The user try to leave a group that he/she is not in, this is checked by GroupPermissionMiddleware.
  

- **500 Internal Server Error**:

    ```json
    {
      "error": "Unknown error occurred"
    }
    ```

---
## **3. Messages API**

### **GET /groups/:groupId/messages**

- **Description**: Retrieve a list of all messages of a group based on the group id, with details such as message id, user id, group id, content, created time and the creator's username.
- **Request Parameters**: Time(ISO 8601 format, eg: 2025-01-01T00:00:00Z)
- **Header**:

```http
Authorization: Bearer <your_token>
X-User-Id: <your_id>
```

- **Response**:

```json
[
  {
    "id": 1,
    "group_id": 1,
    "user_id": 1,
    "content": "Test Message",
    "created_at": "2025-02-01T12:00:00Z",
    "username": "user1"
  },
  {
    "id": 2,
    "group_id": 1,
    "user_id": 1,
    "content": "Test Message",
    "created_at": "2025-02-02T12:00:00Z",
    "username": "user2"
  }
]
```

> **Note**: If the query parameter is provided, only the messages sent after the given time will be returned. if no timestamp or invalid timestamp is given, all messages in this group will be returned.


#### **Error Responses**

- **401 Unauthorized**:

    ```json
    {
      "error": "Missing or invalid token"
    }
    ```

    - **Occurs When**: When the user doesn't have valid token, checked by AuthMiddleware.


- **403 Forbidden**:

    ```json
    {
      "error": "User is not in the group"
    }
    ```

    - **Occurs When**: The user try to leave a group that he/she is not in, this is checked by GroupPermissionMiddleware.

---

### **POST /groups/:groupId/messages**

- **Description**: Send a message to a group based on group ID.
- **Request Parameters**: none
- **Header**:

```http
Authorization: Bearer <your_token>
X-User-Id: <your_id>
```

- **Request**

```json
{
  "content": "Test Message"
}
```

- **Response**:

```json
{
  "message": "Message sent successfully"
}
```
#### **Error Responses**

- **400 Bad Request**:

    ```json
    {
      "error": "Message cannot be empty"
    }
    ```

    - **Occurs When**: When the user try to send an empty message.
  

- **401 Unauthorized**:

    ```json
    {
      "error": "Missing or invalid token"
    }
    ```

    - **Occurs When**: When the user doesn't have valid token, checked by AuthMiddleware.

- **403 Forbidden**:

    ```json
    {
      "error": "User is not in the group"
    }
    ```

    - **Occurs When**: The user try to leave a group that he/she is not in, this is checked by GroupPermissionMiddleware.


---

## Possible Future Enhancements

The following features are not required in the assessment doc, but can be possible potential future improvements:

>**PUT /users/:userId**: 
> 
>Allow users to change their username.
>```sql
>UPDATE users 
>SET username = [new_username]
>WHERE id = [user_id]
>```

>**DELETE /users/:userId**: 
> 
>Allow users to delete their accounts.
>```sql
> DELETE FROM users
> WHERE id = [user_id]
>```

>**PUT /groups/:groupId**: 
> 
>Allow users to change the group name.
>```sql
> UPDATE groups
> SET group_name = [new_group_name]
> WHERE id = [group_id]
>```

>**DELETE /groups/:groupId**: 
>Allow users to delete chatting groups.
> ```sql
> DELETE FROM groups
> WHERE id = [group_id]
>```
