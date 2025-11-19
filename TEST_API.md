# API Testing Guide

## Backend is Running! ✅

Your Laravel API server is running at: **http://127.0.0.1:8000**

## Quick Test

Open your browser and go to:
```
http://127.0.0.1:8000
```

You should see: `{"message":"Niche Plus API"}`

## Test Login with Postman or Browser Extension

**Endpoint:** `POST http://127.0.0.1:8000/api/login`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "email": "admin@nichemagazine.me",
  "password": "password"
}
```

**Expected Response:**
```json
{
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@nichemagazine.me",
    "role": "admin",
    "tier": "vip"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

## Test Other Endpoints

After login, use the token in headers:
```
Authorization: Bearer YOUR_TOKEN_HERE
```

### Get Posts
```
GET http://127.0.0.1:8000/api/posts
```

### Get Awards
```
GET http://127.0.0.1:8000/api/awards
```

### Get Offers
```
GET http://127.0.0.1:8000/api/offers
```

## Next Step: Frontend Setup

Now that the backend is working, let's set up the React Native app!

```bash
cd niche-app
npm install
```

Then update `src/services/api.js` with:
```javascript
const API_URL = 'http://127.0.0.1:8000/api';
```

Start the app:
```bash
npm start
```
