Laravel Store API
================

This is a simple API built using Laravel for managing a store's inventory and orders. It provides endpoints for CRUD operations on products, orders, customers, and more.

### Installation

1. Clone the repository: `git clone https://github.com/khaled50k/laravel-store-api.git`
2. Install dependencies: `composer install`
3. Set up the database: `php artisan migrate`
4. Start the server: `php artisan serve`

### Endpoints

* **Products**
	+ GET `/products`: List all products
	+ GET `/products/{id}`: Show a product by ID
	+ POST `/products`: Create a new product
	+ PUT `/products/{id}`: Update a product
	+ DELETE `/products/{id}`: Delete a product
* **Product Colors**
	+ GET `/product-colors`: List all product colors
	+ GET `/product-colors/{id}`: Show a product color by ID
	+ POST `/product-colors`: Create a new product color
	+ PUT `/product-colors/{id}`: Update a product color
	+ DELETE `/product-colors/{id}`: Delete a product color
* **Product Images**
	+ GET `/product-images`: List all product images
	+ GET `/product-images/{id}`: Show a product image by ID
	+ POST `/product-images`: Create a new product image
	+ DELETE `/product-images/{id}`: Delete a product image
* **Product Sizes**
	+ GET `/product-sizes`: List all product sizes
	+ GET `/product-sizes/{id}`: Show a product size by ID
	+ POST `/product-sizes`: Create a new product size
	+ DELETE `/product-sizes/{id}`: Delete a product size
* **Categories**
	+ GET `/categories`: List all categories
	+ GET `/categories/{id}`: Show a category by ID
	+ POST `/categories`: Create a new category
	+ PUT `/categories/{id}`: Update a category
	+ DELETE `/categories/{id}`: Delete a category
* **Orders**
	+ GET `/orders`: List all orders
	+ GET `/orders/{id}`: Show an order by ID
	+ POST `/orders`: Create a new order
	+ PUT `/orders/{id}`: Update an order
	+ DELETE `/orders/{id}`: Delete an order
* **Order Items**
	+ GET `/order-items`: List all order items
	+ GET `/order-items/{id}`: Show an order item by ID
	+ POST `/order-items`: Create a new order item
	+ PUT `/order-items/{id}`: Update an order item
	+ DELETE `/order-items/{id}`: Delete an order item
* **Customers**
	+ GET `/customers`: List all customers
	+ GET `/customers/{id}`: Show a customer by ID
	+ POST `/customers`: Create a new customer
	+ PUT `/customers/{id}`: Update a customer
	+ DELETE `/customers/{id}`: Delete a customer
* **Payments**
	+ GET `/payments`: List all payments
	+ GET `/payments/{id}`: Show a payment by ID
	+ POST `/payments`: Create a new payment
	+ PUT `/payments/{id}`: Update a payment
	+ DELETE `/payments/{id}`: Delete a payment
* **PayPal**
	+ POST `/paypal/create`: Create a new PayPal payment
	+ POST `/paypal/capture`: Capture a PayPal payment
	+ GET `/paypal/cancel`: Cancel a PayPal payment
	+ GET `/paypal/success`: Success PayPal payment

### Contributing

Contributions are welcome! Please open a pull request to add new features or fix issues.

### License

This project is licensed under the MIT License.