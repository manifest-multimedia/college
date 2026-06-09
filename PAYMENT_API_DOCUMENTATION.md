# Payment Gateway API Documentation

This API allows authorized payment gateway providers and financial applications to query student billing information and record full or partial payments against individual fee items.

---

## 1. Authentication & Security

All API endpoints require secure token-based authentication.

### Authorization Header
Include your API token in the `Authorization` header of every request:

```http
Authorization: Bearer <YOUR_API_TOKEN>
Accept: application/json
```

### Access Control
Access is restricted to authorized external integrations and payment gateways. Providers are issued secure API tokens that grant read access for querying student bills and write access for posting payment transactions.

---

## 2. API Endpoints Reference

### A. Retrieve Student Profile & Active Bills
Retrieves the profile of a student and all their active fee bills (including itemized fees, paid amounts, remaining balances, and payment statuses).

- **URL**: `/api/v1/payments/student`
- **Method**: `GET`
- **Authentication**: Required (Bearer Token)
- **Query Parameters**:
  - `student_id` (Required, string): The student registration number (e.g., `STU001`) or primary ID. Specifies the target student whose billing details are being retrieved.

#### Example Request (cURL)
```bash
curl -X GET "https://college.example.com/api/v1/payments/student?student_id=STU-TEST-99" \
     -H "Authorization: Bearer your_api_token_here" \
     -H "Accept: application/json"
```

#### Example Response (200 OK)
```json
{
  "success": true,
  "student": {
    "id": 15,
    "student_id": "STU-TEST-99",
    "name": "Alice Smith",
    "email": "alice.smith@college.edu",
    "mobile_number": "+233240000000",
    "class": "Computer Science Year 2",
    "cohort": "Cohort 2024",
    "status": "active"
  },
  "bills": [
    {
      "id": 8,
      "bill_reference": "BILL-TEST-REF",
      "academic_year": "2025-2026",
      "semester": "First Semester",
      "total_amount": 1000.00,
      "amount_paid": 250.00,
      "balance": 750.00,
      "payment_percentage": 25.00,
      "status": "partially_paid",
      "billing_date": "2026-06-09T11:40:00Z",
      "items": [
        {
          "id": 24,
          "fee_name": "Tuition Fees",
          "description": "Academic tuition fee item",
          "amount": 600.00,
          "amount_paid": 250.00,
          "balance": 350.00,
          "status": "partially_paid"
        },
        {
          "id": 25,
          "fee_name": "Registration Fees",
          "description": "Semester registration fee item",
          "amount": 400.00,
          "amount_paid": 0.00,
          "balance": 400.00,
          "status": "pending"
        }
      ]
    }
  ]
}
```

---

### B. Retrieve Bill Details
Retrieves detailed information for a specific bill, including its itemized fee components and a history of all payments.

- **URL**: `/api/v1/payments/bills/{id}`
- **Method**: `GET`
- **Authentication**: Required (Bearer Token)
- **URL Parameters**:
  - `id` (Required, integer): The unique ID of the student fee bill.

#### Example Request (cURL)
```bash
curl -X GET "https://college.example.com/api/v1/payments/bills/8" \
     -H "Authorization: Bearer your_api_token_here" \
     -H "Accept: application/json"
```

#### Example Response (200 OK)
```json
{
  "success": true,
  "bill": {
    "id": 8,
    "bill_reference": "BILL-TEST-REF",
    "student": {
      "id": 15,
      "student_id": "STU-TEST-99",
      "name": "Alice Smith"
    },
    "academic_year": "2025-2026",
    "semester": "First Semester",
    "total_amount": 1000.00,
    "amount_paid": 250.00,
    "balance": 750.00,
    "payment_percentage": 25.00,
    "status": "partially_paid",
    "billing_date": "2026-06-09T11:40:00Z",
    "items": [
      {
        "id": 24,
        "fee_name": "Tuition Fees",
        "amount": 600.00,
        "amount_paid": 250.00,
        "balance": 350.00,
        "status": "partially_paid"
      },
      {
        "id": 25,
        "fee_name": "Registration Fees",
        "amount": 400.00,
        "amount_paid": 0.00,
        "balance": 400.00,
        "status": "pending"
      }
    ],
    "payments": [
      {
        "id": 41,
        "amount": 250.00,
        "payment_method": "Mobile Money",
        "reference_number": "REF-API-101",
        "receipt_number": "FP20260609XYZ78",
        "external_receipt": "https://paymentgateway.com/receipt/101",
        "payment_date": "2026-06-09T11:41:00Z",
        "reversed": false
      }
    ]
  }
}
```

---

### C. Record Payment for Selected Fee Item
Records a full or partial payment against a specific fee item. This updates the status and balance of the specific item, and automatically updates the parent bill's overall status, paid amount, and balance.

- **URL**: `/api/v1/payments/pay-item`
- **Method**: `POST`
- **Authentication**: Required (Bearer Token with write permissions)
- **Headers**:
  - `Content-Type: application/json`
- **Request Body Fields**:

| Field Name | Type | Required | Description / Rules |
| :--- | :--- | :--- | :--- |
| `student_fee_bill_item_id` | Integer | Yes | The ID of the specific fee item being paid (e.g. `24` for Tuition). |
| `amount` | Numeric | Yes | The payment amount. Must be greater than or equal to `0.01`. |
| `payment_method` | String | Yes | Payment channel (e.g., `Mobile Money`, `Credit Card`, `Bank Transfer`). Max 50 chars. |
| `reference_number` | String | Yes | Unique transaction ID generated by the payment provider. Max 100 chars. **Must be unique** to prevent double-processing. |
| `external_receipt` | String | No | URL link, document ID, or receipt reference generated by the external provider. |
| `note` | String | No | A short memo or description. Max 255 chars. |

#### Example Request Body
```json
{
  "student_fee_bill_item_id": 24,
  "amount": 350.00,
  "payment_method": "Credit Card",
  "reference_number": "TXN-98231034-ABC",
  "external_receipt": "https://stripe.com/receipts/acct_1032/ch_104",
  "note": "Final Tuition Payment"
}
```

#### Example Response (201 Created)
```json
{
  "success": true,
  "message": "Payment recorded successfully.",
  "payment": {
    "id": 42,
    "amount": 350.00,
    "payment_method": "Credit Card",
    "reference_number": "TXN-98231034-ABC",
    "receipt_number": "FP20260609JH5FA",
    "external_receipt": "https://stripe.com/receipts/acct_1032/ch_104",
    "payment_date": "2026-06-09T11:42:15Z"
  },
  "fee_item": {
    "id": 24,
    "amount": 600.00,
    "amount_paid": 600.00,
    "balance": 0.00,
    "status": "paid"
  },
  "bill": {
    "id": 8,
    "bill_reference": "BILL-TEST-REF",
    "total_amount": 1000.00,
    "amount_paid": 600.00,
    "balance": 400.00,
    "status": "partially_paid"
  }
}
```

---

## 3. Error Handling & Formats

The API communicates errors using standard HTTP status codes and uniform JSON responses.

### Common HTTP Status Codes
- `400 Bad Request`: Missing query parameters or malformed body.
- `401 Unauthorized`: Missing or invalid Bearer API token.
- `403 Forbidden`: Insufficient permissions or token access rights.
- `404 Not Found`: Student, Bill, or Fee Item does not exist.
- `422 Unprocessable Entity`: Data validation failed (e.g. duplicate reference, negative amount).
- `500 Internal Server Error`: System failure or database transaction failure.

### Validation Error Format (422)
When request parameters fail validation:

```json
{
  "message": "The reference number has already been taken.",
  "errors": {
    "reference_number": [
      "The reference number has already been taken."
    ]
  }
}
```

### Authorization Error Format (403)
```json
{
  "success": false,
  "message": "Unauthorized access to student payment details."
}
```

---

## 4. Code Sample (Node.js Integration)

```javascript
const axios = require('axios');

async function processStudentPayment() {
  const token = 'YOUR_API_TOKEN';
  const apiBase = 'https://college.example.com/api';

  try {
    // 1. Fetch student bills & items
    const studentInfo = await axios.get(`${apiBase}/v1/payments/student?student_id=STU-TEST-99`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });

    const activeBill = studentInfo.data.bills[0];
    const tuitionItem = activeBill.items.find(item => item.fee_name === 'Tuition Fees');

    console.log(`Student owe: ${tuitionItem.balance} for Tuition.`);

    if (tuitionItem.balance > 0) {
      // 2. Post payment to specific tuition item
      const paymentResponse = await axios.post(`${apiBase}/v1/payments/pay-item`, {
        student_fee_bill_item_id: tuitionItem.id,
        amount: tuitionItem.balance,
        payment_method: 'Mobile Money',
        reference_number: 'TXN-' + Date.now(),
        external_receipt: 'https://payment-gateway/receipts/r-992',
        note: 'Paying outstanding balance'
      }, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      });

      console.log('Payment Success. Receipt:', paymentResponse.data.payment.receipt_number);
    }
  } catch (error) {
    console.error('Error processing transaction:', error.response ? error.response.data : error.message);
  }
}
```
