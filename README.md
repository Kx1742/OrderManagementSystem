# McDonald's Order System

## Project Overview

This project is a McDonald's Order System that allows customers, VIP customers, and managers to interact with the system. The system includes functionalities for creating orders, managing bots, and processing orders.

## Key Features

1. **Customer View**: Allows customers to create normal orders and view pending and completed orders.
2. **VIP View**: Allows VIP customers to create VIP orders, which are prioritized over normal orders.
3. **Manager View**: Allows managers to add and remove bots, and view all orders and bots.
4. **VIP Order Prioritization**: When a new VIP order is created, it is placed in front of all existing normal orders but behind all existing VIP orders.
5. **Bot Removal**: When the "- Bot" button is clicked, the newest bot is destroyed. If the bot is processing an order, the order is set back to "PENDING" and ready to be processed by another bot.



## How to Run the Project

1. **Clone the Repository**:
   ```sh
   git clone <your-repo-url>
   cd <your-repo-directory>

2. **Install Dependencies**:
   ```sh
   composer install

3. **Run Migrations**:
   To set up your database schema, run the following command:
   ```sh
   php artisan migrate

4. **Start the Server**:
   ```sh
   php artisan serve

5. **Start the Queue Worker**:
   ```sh
   php artisan queue:work

6. **Access the Application**:
   ```sh
   http://127.0.0.1:8000
