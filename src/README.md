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

## Future Enhancements

The following features are not part of the current API but are potential future improvements:

### 1. **Search API**

- **GET /search**
    - **Description**: Enable users to search for tours or highlights based on keywords or categories.
    - **Request Parameters**:
        - `q` (string, optional): Search query.
        - `category` (string, optional): Filter by category (e.g., "Historical").
    - **Response**:
      ```json
      [
        {
          "id": 1,
          "name": "Historical Tour",
          "description": "Explore the rich history of Deventer."
        },
        {
          "id": 101,
          "name": "Old Church",
          "description": "A historical church in the city center."
        }
      ]
      ```

### 2. **Moderator API**

- Approve or reject user-submitted highlights or comments.
    - **Endpoints** (future design):
        - **POST /moderator/approve/:id**: Approve a specific highlight or comment.
        - **POST /moderator/reject/:id**: Reject a specific highlight or comment.

> **Note**: These enhancements are outside the current sprint's scope and are subject to discussion and prioritization.


---

## Other Explanation:

---

### About how to implement: PATCH /tours/:id/customize :

### **Backend Workflow**

#### 1. Validate Tour and Highlights

- Confirm the `tourId` exists in the `Tour` table.
- Verify that the `highlightId`s in `enabledHighlights`, `disabledHighlights`, and `updatedOrder` exist in the
  `Highlight` table.
- Check that the `highlightId`s belong to the specified `tourId` in the `TourHighlight` table.

#### 2. Update Associations

- **Enable Highlights**: Add entries to the `TourHighlight` table for IDs in `enabledHighlights`:
  ```sql
  INSERT INTO TourHighlight (tourID, highlightID)
  VALUES ($1, $2)
  ON CONFLICT DO NOTHING;
  ```

- **Disable Highlights**: Remove entries from the `TourHighlight` table for IDs in `disabledHighlights`:
  ```sql
  DELETE FROM TourHighlight
  WHERE tourID = $1 AND highlightID = $2;
  ```

#### 3. Update Order

- Update the `order` field in the `TourHighlight` table for each highlight in `updatedOrder`:
  ```sql
  UPDATE TourHighlight
  SET "order" = $3
  WHERE tourID = $1 AND highlightID = $2;
  ```

#### 4. Fetch Updated Highlights

- Fetch the customized highlights for the tour:
  ```sql
  SELECT th.highlightID, h.name, th."order", 
         CASE 
           WHEN th.highlightID IN (SELECT highlightID FROM TourHighlight WHERE tourID = $1)
           THEN 'enabled' 
           ELSE 'disabled' 
         END AS status
  FROM Highlight h
  LEFT JOIN TourHighlight th ON h.id = th.highlightID
  WHERE th.tourID = $1
  ORDER BY th."order";
  ```

---

### **Integration with the Database Design**

1. **`TourHighlight` Table**:
    - Used to manage the relationships between tours and highlights.
    - The `order` field is critical for determining the sequence of highlights within a tour.

2. **Customizing Highlights**:
    - `enabledHighlights` adds entries to `TourHighlight`.
    - `disabledHighlights` removes entries from `TourHighlight`.

3. **Reordering Highlights**:
    - The `order` field in `TourHighlight` is updated using the `updatedOrder` array.

---