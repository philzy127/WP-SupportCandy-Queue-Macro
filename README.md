# SupportCandy Queues

This plugin adds a `{{queue_count}}` macro to SupportCandy, allowing you to show customers their real-time position in the queue.

## Description

This WordPress plugin extends the functionality of SupportCandy by adding a new `{{queue_count}}` email macro. When an email is sent, this macro is dynamically replaced with the number of open tickets of the same type (e.g., category, priority) as the ticket that triggered the email.

This is useful for managing customer expectations by giving them an idea of the current support load for their specific issue.

## Features

*   **Real-time Queue Count:** Automatically calculates and displays the number of open tickets in the same queue.
*   **Seamless Integration:** Adds a `{{queue_count}}` macro directly to the SupportCandy macro list.
*   **Configurable Statuses:** You can define which ticket statuses are considered "non-closed" from the settings page, giving you full control over what's included in the count.
*   **Customizable Ticket Type:** Determine the queue based on any standard or custom ticket field (e.g., category, priority, or a custom "Department" field).

## Installation

1.  Download the plugin files.
2.  Upload the `WP-SupportCandy-Queue-Macro` directory to your `/wp-content/plugins/` directory.
3.  Activate the plugin through the 'Plugins' menu in WordPress.

## Usage

1.  **Configure the Plugin:**
    *   Navigate to **SupportCandy Queues** from the main WordPress admin menu.
    *   In the **Non-Closed Statuses** section, select the ticket statuses that should be counted as part of the queue.
    *   In the **Ticket Type Field** dropdown, select the field that distinguishes your queues (e.g., `category`, `priority`, or a custom field).
    *   Click **Save Settings**.

2.  **Add the Macro to Your Emails:**
    *   Go to your SupportCandy email templates.
    *   Find "Queue Count" in the list of available macros.
    *   Insert the `{{queue_count}}` macro where you want the queue count to appear.

Now, when an email is sent, the macro will be replaced with the current queue count for that ticket's type.