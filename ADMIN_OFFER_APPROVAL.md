# Admin Offer Approval System

## Overview
Suppliers can create ads (offers), but they require admin approval before being visible to regular users.

## Database Changes

### Migration
Run this migration to add approval fields:
```bash
php artisan migrate
```

This adds to the `offers` table:
- `status` (enum: pending, approved, rejected) - default: pending
- `rejection_reason` (text, nullable)
- `reviewed_at` (timestamp, nullable)
- `reviewed_by` (foreign key to users table, nullable)

## API Endpoints

### Admin Endpoints (require admin role)

#### Get Pending Offers
```
GET /api/admin/offers/pending
```
Returns all offers waiting for approval with supplier details.

#### Get Approved Offers
```
GET /api/admin/offers/approved
```
Returns all approved offers.

#### Get Rejected Offers
```
GET /api/admin/offers/rejected
```
Returns all rejected offers.

#### Get All Offers (with filters)
```
GET /api/admin/offers?status=pending&search=wedding&supplier_id=5
```
Query parameters:
- `status` - Filter by status (pending/approved/rejected)
- `search` - Search in title, description, city
- `supplier_id` - Filter by specific supplier

#### Approve an Offer
```
POST /api/admin/offers/{id}/approve
```
Approves a pending offer. Returns error if offer is not pending.

Response:
```json
{
  "message": "Offer approved successfully",
  "offer": { ... }
}
```

#### Reject an Offer
```
POST /api/admin/offers/{id}/reject
Content-Type: application/json

{
  "reason": "Images are not clear enough"
}
```
Rejects a pending offer with a reason. The reason is required.

Response:
```json
{
  "message": "Offer rejected successfully",
  "offer": { ... }
}
```

#### Get Statistics
```
GET /api/admin/offers/statistics
```
Returns:
```json
{
  "total": 150,
  "pending": 12,
  "approved": 130,
  "rejected": 8,
  "pending_today": 3
}
```

### Supplier Endpoints

#### Create Offer
```
POST /api/offers
POST /api/supplier/offers
```
When a supplier creates an offer, it's automatically set to `status: 'pending'`.

Response includes:
```json
{
  "message": "Offer created successfully and is pending admin approval",
  "offer": { ... }
}
```

#### View Own Offers
```
GET /api/offers?my_offers=true
GET /api/supplier/offers
```
Suppliers can see all their offers regardless of status.

#### Supplier Stats
```
GET /api/supplier/stats
```
Now includes:
```json
{
  "total_offers": 10,
  "pending_offers": 2,
  "approved_offers": 7,
  "rejected_offers": 1,
  ...
}
```

### Public Endpoints

#### Get Offers
```
GET /api/offers
```
Regular users only see **approved** offers. Admins see all offers.

## Offer Model Methods

```php
$offer->isPending()    // Returns true if status is 'pending'
$offer->isApproved()   // Returns true if status is 'approved'
$offer->isRejected()   // Returns true if status is 'rejected'

// Query scopes
Offer::pending()->get()
Offer::approved()->get()
Offer::rejected()->get()

// Relationships
$offer->reviewer       // User who approved/rejected
$offer->supplier       // Supplier who created it
```

## Workflow

1. **Supplier creates offer** → Status: `pending`
2. **Admin reviews** → Can see in `/api/admin/offers/pending`
3. **Admin approves** → Status: `approved`, visible to all users
4. **OR Admin rejects** → Status: `rejected`, supplier can see rejection reason

## Testing

### 1. Create an offer as supplier
```bash
curl -X POST http://localhost:8000/api/offers \
  -H "Authorization: Bearer {supplier_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Wedding Photography",
    "photo": "photos/wedding.jpg",
    "price": 5000,
    "description": "Professional wedding photography",
    "city": "Dubai",
    "whatsapp": "+971501234567"
  }'
```

### 2. View pending offers as admin
```bash
curl http://localhost:8000/api/admin/offers/pending \
  -H "Authorization: Bearer {admin_token}"
```

### 3. Approve offer as admin
```bash
curl -X POST http://localhost:8000/api/admin/offers/1/approve \
  -H "Authorization: Bearer {admin_token}"
```

### 4. Reject offer as admin
```bash
curl -X POST http://localhost:8000/api/admin/offers/1/reject \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{"reason": "Images quality is too low"}'
```

## Notes

- Only **pending** offers can be approved or rejected
- Rejection requires a reason (max 500 characters)
- Approved offers are visible to all users
- Suppliers can always see their own offers regardless of status
- The `reviewed_by` field tracks which admin approved/rejected
- The `reviewed_at` timestamp records when the action was taken
