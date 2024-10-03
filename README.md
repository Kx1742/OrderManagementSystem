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
   git clone https://github.com/Kx1742/se-take-home-assignment.git
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

## Using Jobs for Order Processing

### Overview

The `ProcessOrderJob` is a Laravel job responsible for handling the processing of orders by bots. It ensures that orders are processed asynchronously, allowing the application to manage multiple orders concurrently without blocking the main execution thread.

### Key Features

- **Queueable**: The job implements the `ShouldQueue` interface, meaning it is dispatched to a queue and processed by a queue worker.
  
- **Timeout and Retries**: The job has a timeout of 120 seconds and will attempt to process the order up to 5 times in case of failure.
  
- **Order Processing**: The job simulates order processing by logging countdown messages and updating both the order and bot statuses upon successful completion.
  
- **Error Handling**: The job includes error handling to log any exceptions that occur during order processing. If an exception is thrown, it re-throws the error to mark the job as failed.

### How It Works

1. The job checks if the bot is available and in the correct status.
2. If conditions are met, it simulates order processing by using a delay (e.g., `sleep`).
3. Once processing is complete, it updates the order's status to `completed` and sets the bot's status to `idle`.
4. If any issues arise, such as the bot not being in the correct state or an error during processing, the job logs the error and re-attempts based on the retry policy.

