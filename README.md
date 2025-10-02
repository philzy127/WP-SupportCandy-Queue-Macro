# SupportCandy Queues

This plugin dynamically calculates the number of open tickets in a queue and saves it to a custom field on ticket creation. This allows you to use SupportCandy's native macros to show customers their position in the queue.

## Description

This WordPress plugin extends the functionality of SupportCandy by calculating a real-time queue count when a new ticket is created. Instead of registering a new, non-standard macro, this plugin leverages SupportCandy's robust custom field system.

You create a custom field in SupportCandy (e.g., "Queue Position"), and this plugin will automatically populate it with the correct queue count for each new ticket. You can then use the standard SupportCandy macro for that custom field (e.g., `{{ticket.your_custom_field_slug}}`) in your email templates.

This is useful for managing customer expectations by giving them an idea of the current support load for their specific issue.

## Features

*   **Real-time Queue Count:** Automatically calculates and saves the number of open tickets to a custom field for each new ticket.
*   **Seamless Integration:** Uses SupportCandy's native custom field and macro system for maximum compatibility and reliability.
*   **Configurable Statuses:** You can define which ticket statuses are considered "non-closed" from the settings page, giving you full control over what's included in the count.
*   **Customizable Ticket Type:** Determine the queue based on any standard or custom ticket field (e.g., category, priority, or a custom "Department" field).
*   **Live Test Feature:** A test button on the settings page allows you to see the current queue counts for all ticket types in real-time.

## Installation

1.  Download the plugin files.
2.  Upload the `WP-SupportCandy-Queue-Macro` directory to your `/wp-content/plugins/` directory.
3.  Activate the plugin through the 'Plugins' menu in WordPress.

## Usage

1.  **Create a Custom Field in SupportCandy:**
    *   Navigate to **Support** → **Custom Fields**.
    *   Create a new **Text Field**. You can name it whatever you like, such as "Queue Position" or "Queue Count".
    *   Take note of the **slug** that is generated for this field (e.g., `cust_123`). You will need it in the next step.

2.  **Configure the Plugin:**
    *   Navigate to **SupportCandy Queues** from the main WordPress admin menu.
    *   In the **Non-Closed Statuses** section, select the ticket statuses that should be counted as part of the queue.
    *   In the **Ticket Type Field** dropdown, select the field that distinguishes your queues (e.g., `category`, `priority`, or a custom field).
    *   In the **Placeholder Field Slug** input box, enter the slug of the custom field you created in step 1.
    *   Click **Save Settings**.

3.  **Add the Macro to Your Emails:**
    *   Go to your SupportCandy email templates.
    *   Find the custom field you created in the list of available macros.
    *   Insert the macro (e.g., `{{ticket.cust_123}}`) where you want the queue count to appear.

Now, when a new ticket is created, the plugin will automatically calculate the queue count and save it to your custom field, and the correct value will be displayed in the email.