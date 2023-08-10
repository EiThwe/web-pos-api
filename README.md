# Web-POS-api

## Api Reference
_All api services need bearer_token to access_

### Authentication

__Login__ `POST`

```
 http://127.0.0.1:8000/api/v1/login
```

Arguments  |  Type  |  Status | Description 
-----------|--------|---------|------------
email      | string | **Required** |tth@gmail.com(admin)
password   | string | **Required** |11223344

### Dashboard

__Register__ `POST`

_Only admin can register the accounts_

```
 http://127.0.0.1:8000/api/v1/register
```

Arguments  |  Type  |  Status | Description 
-----------|--------|---------|------------
name      | string | **Required**| Chit Chit
email      | string | **Required**| cc@gmail.com
password   | string | **Required** |11223344  

  __Logout__ `POST`

```
 http://127.0.0.1:8000/api/v1/logout
```
### Profile

__All Devices__ `GET`

```
 http://127.0.0.1:8000/api/v1/devices
```

__Logout All Devices__ `POST`

```
 http://127.0.0.1:8000/api/v1/logout-all
```

### Brand

__Get Products__ `GET`

```
 http://127.0.0.1:8000/api/v1/brands?page={id}
```

__Get Single Brand__ `GET`

```
 http://127.0.0.1:8000/api/v1/brands/{id}
```

__Create Brand__ `POST`

```
 http://127.0.0.1:8000/api/v1/brands
```

Arguments  |  Type  |  Status | Description 
-----------|--------|---------|------------
name      | string | **Required** |MAMA(min:3)
company      | string | **Required** |MAMA co.ltd
information   | string | **Required** |min:50
photo          |url|nullable|default photo

__Update Brand__ `PUT/PATCH`

```
 http://127.0.0.1:8000/api/v1/brands/{id}
```
__You can update single parameter or more__

Arguments  |  Type  |  Status | Description 
-----------|--------|---------|------------
name      | string | nullable| MAMA 3(min:3)
company      | string | nullable| MA MA
information   | string | nullable |min:50
photo  | url | nullable|default photo

__Delete Brand__ `DELETE`

_Only admin can delete_

```
 http://127.0.0.1:8000/api/v1/brands/{id}
```
### Product

__Get Products__ `GET`

```
 http://127.0.0.1:8000/api/v1/products?page={id}
```

__Get Single Product__ `GET`

```
 http://127.0.0.1:8000/api/v1/products/{id}
```

__Create Product__ `POST`

```
 http://127.0.0.1:8000/api/v1/products
```

Arguments  |  Type  |  Status | Description 
-----------|--------|---------|------------
name      | string | **Required** |Noodle(min:3)
brand_id      |integer  | **Required** |3
actual_price   | interger | **Required** |140(min:100)
sale_price   | interger | **Required** |190(min:100)
unit      |string  | **Required**| pack
more_information   | string | nullable |min:50
photo  | url | required|no default photo



__Update Product__ `PUT/PATCH`

```
 http://127.0.0.1:8000/api/v1/products/{id}
```
__You can update single parameter or more__

Arguments  |  Type  |  Status | Description 
-----------|--------|---------|------------
name      | string | nullable |Noodle(min:3)
brand_id      |integer  | nullable |3
actual_price   | interger | nullable |140(min:100)
sale_price   | interger | nullable |190(min:100)
unit      |string  | nullable| pack
more_information   | string | nullable |min:50
photo  | url | nullable|no default photo

__Delete Product__ `DELETE`

_Only admin can delete_

```
 http://127.0.0.1:8000/api/v1/products/{id}
```
### Stock

__Get Stocks__ `GET`

```
 http://127.0.0.1:8000/api/v1/stocks?page={id}
```

__Get Single Stock__ `GET`

```
 http://127.0.0.1:8000/api/v1/stocks/{id}
```

__Create Stock__ `POST`

```
 http://127.0.0.1:8000/api/v1/stocks
```

Arguments  |  Type  |  Status | Description 
-----------|--------|---------|------------
product_id      | integer | **Required** |3
quantity      | integer | **Required** |10
more_information   | string | nullable |min:50

__Update Stock__ `PUT/PATCH`

```
 http://127.0.0.1:8000/api/v1/stocks{id}
```
__You can update single parameter or more__

Arguments  |  Type  |  Status | Description 
-----------|--------|---------|------------
product_id      | integer | integer |3
quantity      | integer | integer |10
more_information   | string | nullable |min:50

__Delete Stock__ `DELETE`

_Only admin can delete_

```
 http://127.0.0.1:8000/api/v1/stocks/{id}
```

### Voucher

__Get Vouchers__ `GET`

```
 http://127.0.0.1:8000/api/v1/vouchers?page={id}
```

__Get Single Voucher__ `GET`

```
 http://127.0.0.1:8000/api/v1/vouchers/{id}
```

__Update Voucher__ `PUT/PATCH`

```
 http://127.0.0.1:8000/api/v1/vouchers{id}
```
__You can update single parameter or more__

Arguments  |  Type  |  Status | Description 
-----------|--------|---------|------------
customer      | string | nullable |Aung Aung(min: 3)
phone      | integer | nullable |09969969969(min: 6)
net_total  | numeric | nullable | 100
tax  | numeric | nullable | 10
total  | numeric | nullable | 110


__Delete Voucher__ `DELETE`

_Only admin can delete_

```
 http://127.0.0.1:8000/api/v1/vouchers/{id}
```

### Sale

__Checkout__ `POST`

```
 http://127.0.0.1:8000/api/v1/checkout
```

Arguments  |  Type  |  Status | Description 
-----------|--------|---------|------------
customer      | string | nullable |Aung Aung(min: 3)
phone      | integer | nullable |09969969969(min: 6)
voucher_records   | array | **Required** | [ { product_id, quantity } ]

